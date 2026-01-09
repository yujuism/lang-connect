import React, { useState, useEffect, useCallback, useRef } from "react";
import { createRoot } from "react-dom/client";
import { Document, Page, pdfjs } from "react-pdf";
import "react-pdf/dist/Page/AnnotationLayer.css";
import "react-pdf/dist/Page/TextLayer.css";

// Set worker source
pdfjs.GlobalWorkerOptions.workerSrc = `//unpkg.com/pdfjs-dist@${pdfjs.version}/build/pdf.worker.min.mjs`;

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func(...args), wait);
    };
}

function CollaborativePdfViewer({
    sessionId,
    currentUserId,
    partnerName,
    partnerId,
    isReadOnly = false,
    pdfUrl: initialPdfUrl = null,
    initialHighlights = [],
    initialDrawings = [],
    csrfToken,
    onPdfChange,
}) {
    const [pdfUrl, setPdfUrl] = useState(initialPdfUrl);
    const [numPages, setNumPages] = useState(null);
    const [currentPage, setCurrentPage] = useState(1);
    const [scale, setScale] = useState(1.0);
    const [highlights, setHighlights] = useState(initialHighlights);
    const [isRemoving, setIsRemoving] = useState(false);
    const [showRemoveConfirm, setShowRemoveConfirm] = useState(false);
    const [partnerPage, setPartnerPage] = useState(null);
    const [partnerCursor, setPartnerCursor] = useState(null); // { x, y, page }
    const [partnerViewport, setPartnerViewport] = useState(null); // { scrollTop, scrollLeft, scale }
    const [partnerOnline, setPartnerOnline] = useState(false);
    const [isFollowing, setIsFollowing] = useState(false);
    const isFollowingRef = useRef(false);
    const scaleRef = useRef(scale);
    const [selectedText, setSelectedText] = useState(null);
    const [showHighlightMenu, setShowHighlightMenu] = useState(false);
    const [menuPosition, setMenuPosition] = useState({ x: 0, y: 0 });
    const containerRef = useRef(null);
    const pageRef = useRef(null);
    const canvasRef = useRef(null);
    const echoChannelRef = useRef(null);
    const partnerLastSeenRef = useRef(null);
    const [isDrawing, setIsDrawing] = useState(false);
    const [drawingMode, setDrawingMode] = useState(null); // 'pen' | 'text' | null
    const [drawings, setDrawings] = useState(initialDrawings); // Array of drawing strokes/text
    const [currentStroke, setCurrentStroke] = useState(null);
    const [textInput, setTextInput] = useState(null); // { x, y } for text placement
    const [drawingColor, setDrawingColor] = useState('#ef4444'); // Red default
    const [pageSize, setPageSize] = useState({ width: 0, height: 0 }); // For relative positioning
    const [partnerSelection, setPartnerSelection] = useState(null); // Partner's text selection rects
    const [partnerCurrentStroke, setPartnerCurrentStroke] = useState(null); // Partner's stroke being drawn in real-time
    const [partnerTextInput, setPartnerTextInput] = useState(null); // Partner's text being typed in real-time

    // Keep refs in sync with state for use in callbacks
    useEffect(() => {
        isFollowingRef.current = isFollowing;
    }, [isFollowing]);

    useEffect(() => {
        scaleRef.current = scale;
    }, [scale]);

    // WebSocket connection
    useEffect(() => {
        if (isReadOnly || typeof window.Echo === "undefined") return;

        const channel = window.Echo.private(`session.${sessionId}`);
        echoChannelRef.current = channel;

        // Listen for highlight changes
        channel.listen(".pdf.highlight", (event) => {
            if (event.user_id === currentUserId) return;
            setHighlights(event.highlights);
        });

        // Listen for PDF changes (upload/remove by partner)
        channel.listenForWhisper("pdf-changed", (event) => {
            if (event.user_id === currentUserId) return;
            setPdfUrl(event.pdf_url);
            setHighlights(event.highlights || []);
            if (!event.pdf_url) {
                setNumPages(null);
                setCurrentPage(1);
            }
        });

        // Listen for partner page position
        channel.listenForWhisper("pdf-page", (event) => {
            if (event.user_id === currentUserId) return;
            setPartnerPage(event.page);
            partnerLastSeenRef.current = Date.now();
            setPartnerOnline(true);

            // Use ref to get current value
            if (isFollowingRef.current) {
                setCurrentPage(event.page);
            }
        });

        // Listen for partner cursor
        channel.listenForWhisper("pdf-cursor", (event) => {
            if (event.user_id === currentUserId) return;
            setPartnerCursor({ x: event.x, y: event.y, page: event.page });
            partnerLastSeenRef.current = Date.now();
            setPartnerOnline(true);
        });

        // Listen for partner viewport (scroll + zoom)
        channel.listenForWhisper("pdf-viewport", (event) => {
            if (event.user_id === currentUserId) return;
            setPartnerViewport({
                scrollTop: event.scrollTop,
                scrollLeft: event.scrollLeft,
                scale: event.scale,
            });
            partnerLastSeenRef.current = Date.now();
            setPartnerOnline(true);

            // Apply viewport when following
            if (isFollowingRef.current && containerRef.current) {
                setScale(event.scale);
                // Delay scroll to allow scale change to render
                setTimeout(() => {
                    if (containerRef.current) {
                        containerRef.current.scrollTop = event.scrollTop;
                        containerRef.current.scrollLeft = event.scrollLeft;
                    }
                }, 10);
            }
        });

        // Listen for presence
        channel.listenForWhisper("presence", (event) => {
            if (event.user_id === currentUserId) return;
            partnerLastSeenRef.current = Date.now();
            setPartnerOnline(true);
        });

        // Listen for drawing changes (finished strokes)
        channel.listenForWhisper("pdf-drawing", (event) => {
            if (event.user_id === currentUserId) return;
            setDrawings(event.drawings);
        });

        // Listen for real-time stroke updates (while partner is drawing)
        channel.listenForWhisper("pdf-stroke", (event) => {
            if (event.user_id === currentUserId) return;
            setPartnerCurrentStroke(event.stroke);
        });

        // Listen for real-time text input (while partner is typing)
        channel.listenForWhisper("pdf-text-input", (event) => {
            if (event.user_id === currentUserId) return;
            setPartnerTextInput(event.textInput);
        });

        // Listen for partner text selection
        channel.listenForWhisper("pdf-selection", (event) => {
            if (event.user_id === currentUserId) return;
            setPartnerSelection(event.selection);
        });

        // Send presence heartbeat
        const heartbeatInterval = setInterval(() => {
            channel.whisper("presence", { user_id: currentUserId });
        }, 3000);

        channel.whisper("presence", { user_id: currentUserId });

        // Check partner online status
        const onlineCheckInterval = setInterval(() => {
            if (partnerLastSeenRef.current) {
                const online = Date.now() - partnerLastSeenRef.current < 10000;
                setPartnerOnline(online);
                if (!online) setPartnerCursor(null);
            }
        }, 5000);

        return () => {
            clearInterval(heartbeatInterval);
            clearInterval(onlineCheckInterval);
            window.Echo.leave(`session.${sessionId}`);
        };
    }, [sessionId, currentUserId, isReadOnly]);

    // Broadcast page change
    useEffect(() => {
        if (isReadOnly || !echoChannelRef.current) return;
        echoChannelRef.current.whisper("pdf-page", {
            user_id: currentUserId,
            page: currentPage,
        });
    }, [currentPage, currentUserId, isReadOnly]);

    // Save highlights to server
    const saveHighlights = useCallback(
        debounce(async (newHighlights) => {
            if (isReadOnly) return;
            try {
                await fetch(`/sessions/${sessionId}/pdf/highlights`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                    },
                    body: JSON.stringify({ highlights: newHighlights }),
                });
            } catch (e) {
                console.error("Failed to save highlights:", e);
            }
        }, 1000),
        [sessionId, csrfToken, isReadOnly]
    );

    // Save drawings to server
    const saveDrawings = useCallback(
        debounce(async (newDrawings) => {
            if (isReadOnly) return;
            try {
                await fetch(`/sessions/${sessionId}/pdf/drawings`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                    },
                    body: JSON.stringify({ drawings: newDrawings }),
                });
            } catch (e) {
                console.error("Failed to save drawings:", e);
            }
        }, 1000),
        [sessionId, csrfToken, isReadOnly]
    );

    // Broadcast highlights
    const broadcastHighlights = useCallback(
        (newHighlights) => {
            if (isReadOnly || !echoChannelRef.current) return;
            fetch(`/sessions/${sessionId}/pdf/broadcast`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: JSON.stringify({ highlights: newHighlights }),
            }).catch(console.error);
        },
        [sessionId, csrfToken, isReadOnly]
    );

    // Broadcast cursor position (throttled)
    const lastCursorBroadcast = useRef(0);
    const broadcastCursor = useCallback(
        (x, y) => {
            if (isReadOnly || !echoChannelRef.current) return;
            const now = Date.now();
            if (now - lastCursorBroadcast.current < 50) return; // Throttle to 20fps
            lastCursorBroadcast.current = now;
            echoChannelRef.current.whisper("pdf-cursor", {
                user_id: currentUserId,
                x,
                y,
                page: currentPage,
            });
        },
        [currentUserId, currentPage, isReadOnly]
    );

    // Broadcast viewport (scroll + zoom) - throttled
    const lastViewportBroadcast = useRef(0);
    const broadcastViewport = useCallback(() => {
        if (isReadOnly || !echoChannelRef.current || !containerRef.current) return;
        const now = Date.now();
        if (now - lastViewportBroadcast.current < 100) return; // Throttle to 10fps
        lastViewportBroadcast.current = now;
        echoChannelRef.current.whisper("pdf-viewport", {
            user_id: currentUserId,
            scrollTop: containerRef.current.scrollTop,
            scrollLeft: containerRef.current.scrollLeft,
            scale: scaleRef.current,
        });
    }, [currentUserId, isReadOnly]);

    // Handle scroll for viewport sync
    const handleScroll = useCallback(() => {
        if (isReadOnly) return;
        broadcastViewport();
    }, [broadcastViewport, isReadOnly]);

    // Broadcast drawings
    const broadcastDrawings = useCallback(
        (newDrawings) => {
            if (isReadOnly || !echoChannelRef.current) return;
            echoChannelRef.current.whisper("pdf-drawing", {
                user_id: currentUserId,
                drawings: newDrawings,
            });
        },
        [currentUserId, isReadOnly]
    );

    // Get position relative to PDF page (for accurate positioning)
    const getRelativePosition = useCallback((e) => {
        const pageEl = pageRef.current;
        if (!pageEl) return null;
        const rect = pageEl.getBoundingClientRect();
        // Return position as percentage of page size for scale-independent positioning
        return {
            x: ((e.clientX - rect.left) / rect.width) * 100,
            y: ((e.clientY - rect.top) / rect.height) * 100,
        };
    }, []);

    // Handle drawing start
    const handleDrawingStart = useCallback(
        (e) => {
            if (isReadOnly || !drawingMode) return;

            // If clicking on the text input, don't start new text
            if (e.target.tagName === 'INPUT') return;

            const pos = getRelativePosition(e);
            if (!pos) return;

            if (drawingMode === 'pen') {
                e.preventDefault();
                setIsDrawing(true);
                setCurrentStroke({
                    type: 'stroke',
                    points: [pos],
                    color: drawingColor,
                    page: currentPage,
                    userId: currentUserId,
                });
            } else if (drawingMode === 'text') {
                e.preventDefault();
                e.stopPropagation();
                // Set new text input position
                const newInput = { x: pos.x, y: pos.y, text: '' };
                setTextInput(newInput);
                // Broadcast to partner that we're starting to type
                if (echoChannelRef.current) {
                    echoChannelRef.current.whisper("pdf-text-input", {
                        user_id: currentUserId,
                        textInput: { ...newInput, page: currentPage, color: drawingColor },
                    });
                }
            }
        },
        [isReadOnly, drawingMode, drawingColor, currentPage, currentUserId, getRelativePosition]
    );

    // Broadcast current stroke in real-time (throttled)
    const lastStrokeBroadcast = useRef(0);
    const broadcastCurrentStroke = useCallback(
        (stroke) => {
            if (isReadOnly || !echoChannelRef.current) return;
            const now = Date.now();
            if (now - lastStrokeBroadcast.current < 30) return; // Throttle to ~33fps
            lastStrokeBroadcast.current = now;
            echoChannelRef.current.whisper("pdf-stroke", {
                user_id: currentUserId,
                stroke,
            });
        },
        [currentUserId, isReadOnly]
    );

    // Handle drawing move
    const handleDrawingMove = useCallback(
        (e) => {
            if (!isDrawing || !currentStroke) return;
            const pos = getRelativePosition(e);
            if (!pos) return;

            const newStroke = {
                ...currentStroke,
                points: [...currentStroke.points, pos],
            };
            setCurrentStroke(newStroke);
            broadcastCurrentStroke(newStroke);
        },
        [isDrawing, currentStroke, getRelativePosition, broadcastCurrentStroke]
    );

    // Handle drawing end
    const handleDrawingEnd = useCallback(() => {
        if (!isDrawing || !currentStroke) return;
        setIsDrawing(false);

        // Only save if stroke has meaningful length
        if (currentStroke.points.length > 2) {
            const newDrawings = [...drawings, { ...currentStroke, id: `${Date.now()}-${currentUserId}` }];
            setDrawings(newDrawings);
            broadcastDrawings(newDrawings);
            saveDrawings(newDrawings);
        }
        setCurrentStroke(null);

        // Clear the real-time stroke on partner's side
        if (echoChannelRef.current) {
            echoChannelRef.current.whisper("pdf-stroke", {
                user_id: currentUserId,
                stroke: null,
            });
        }
    }, [isDrawing, currentStroke, drawings, currentUserId, broadcastDrawings, saveDrawings]);

    // Broadcast text input in real-time
    const broadcastTextInput = useCallback(
        (input) => {
            if (isReadOnly || !echoChannelRef.current) return;
            echoChannelRef.current.whisper("pdf-text-input", {
                user_id: currentUserId,
                textInput: input ? { ...input, page: currentPage, color: drawingColor } : null,
            });
        },
        [currentUserId, currentPage, drawingColor, isReadOnly]
    );

    // Handle text input change
    const handleTextInputChange = useCallback(
        (newText) => {
            const newInput = { ...textInput, text: newText };
            setTextInput(newInput);
            broadcastTextInput(newInput);
        },
        [textInput, broadcastTextInput]
    );

    // Handle text submit
    const handleTextSubmit = useCallback(() => {
        if (!textInput || !textInput.text.trim()) {
            setTextInput(null);
            broadcastTextInput(null); // Clear partner's view
            return;
        }

        const newDrawing = {
            id: `${Date.now()}-${currentUserId}`,
            type: 'text',
            x: textInput.x,
            y: textInput.y,
            text: textInput.text.trim(),
            color: drawingColor,
            page: currentPage,
            userId: currentUserId,
        };

        const newDrawings = [...drawings, newDrawing];
        setDrawings(newDrawings);
        broadcastDrawings(newDrawings);
        saveDrawings(newDrawings);
        setTextInput(null);
        broadcastTextInput(null); // Clear partner's view
    }, [textInput, drawingColor, currentPage, currentUserId, drawings, broadcastDrawings, broadcastTextInput, saveDrawings]);

    // Delete drawing
    const deleteDrawing = useCallback(
        (id) => {
            const newDrawings = drawings.filter((d) => d.id !== id);
            setDrawings(newDrawings);
            broadcastDrawings(newDrawings);
            saveDrawings(newDrawings);
        },
        [drawings, broadcastDrawings, saveDrawings]
    );

    // Clear all drawings on current page
    const clearDrawings = useCallback(() => {
        const newDrawings = drawings.filter((d) => d.page !== currentPage);
        setDrawings(newDrawings);
        broadcastDrawings(newDrawings);
        saveDrawings(newDrawings);
    }, [drawings, currentPage, broadcastDrawings, saveDrawings]);

    // Broadcast text selection (throttled)
    const lastSelectionBroadcast = useRef(0);
    const broadcastSelection = useCallback(() => {
        if (isReadOnly || !echoChannelRef.current) return;
        const now = Date.now();
        if (now - lastSelectionBroadcast.current < 50) return; // Throttle to 20fps
        lastSelectionBroadcast.current = now;

        const selection = window.getSelection();
        const pageEl = pageRef.current;

        if (!selection || selection.isCollapsed || !pageEl) {
            // No selection - clear partner's view
            echoChannelRef.current.whisper("pdf-selection", {
                user_id: currentUserId,
                selection: null,
            });
            return;
        }

        const pageRect = pageEl.getBoundingClientRect();
        const rects = [];

        // Get all selection rects
        for (let i = 0; i < selection.rangeCount; i++) {
            const range = selection.getRangeAt(i);
            const clientRects = range.getClientRects();
            for (let j = 0; j < clientRects.length; j++) {
                const r = clientRects[j];
                rects.push({
                    top: r.top - pageRect.top,
                    left: r.left - pageRect.left,
                    width: r.width,
                    height: r.height,
                });
            }
        }

        if (rects.length > 0) {
            echoChannelRef.current.whisper("pdf-selection", {
                user_id: currentUserId,
                selection: { rects, page: currentPage },
            });
        }
    }, [currentUserId, currentPage, isReadOnly]);

    // Broadcast viewport on zoom change
    useEffect(() => {
        if (isReadOnly || !echoChannelRef.current) return;
        broadcastViewport();
    }, [scale, broadcastViewport, isReadOnly]);

    // Handle mouse move for cursor tracking and selection broadcast
    const handleMouseMove = useCallback(
        (e) => {
            if (isReadOnly) return;
            const pageEl = pageRef.current;
            const containerRect = containerRef.current?.getBoundingClientRect();
            if (pageEl && containerRect) {
                const pageRect = pageEl.getBoundingClientRect();
                // Use page-relative position for cursor tracking
                const x = e.clientX - pageRect.left;
                const y = e.clientY - pageRect.top;
                broadcastCursor(x, y);
            }

            // Broadcast selection while dragging (if mouse is down)
            if (e.buttons === 1 && !drawingMode) {
                broadcastSelection();
            }
        },
        [broadcastCursor, broadcastSelection, isReadOnly, drawingMode]
    );

    // Handle text selection
    const handleMouseUp = useCallback(() => {
        if (isReadOnly || drawingMode) return; // Don't select text when in drawing mode

        const selection = window.getSelection();
        const text = selection.toString().trim();

        if (text && text.length > 0) {
            const range = selection.getRangeAt(0);
            const rect = range.getBoundingClientRect();
            const pageEl = pageRef.current;
            const containerRect = containerRef.current?.getBoundingClientRect();

            if (pageEl && containerRect) {
                const pageRect = pageEl.getBoundingClientRect();
                setSelectedText({
                    text,
                    page: currentPage,
                    // Store position relative to page element (not container) for proper positioning
                    rects: Array.from(range.getClientRects()).map((r) => ({
                        top: r.top - pageRect.top,
                        left: r.left - pageRect.left,
                        width: r.width,
                        height: r.height,
                    })),
                });
                setMenuPosition({
                    x: rect.left + rect.width / 2 - containerRect.left,
                    y: rect.top - containerRect.top - 10,
                });
                setShowHighlightMenu(true);
            }
        } else {
            setShowHighlightMenu(false);
            setSelectedText(null);
        }

        // Clear selection broadcast after mouse up (selection is done)
        if (echoChannelRef.current) {
            echoChannelRef.current.whisper("pdf-selection", {
                user_id: currentUserId,
                selection: null,
            });
        }
    }, [currentPage, isReadOnly, drawingMode, currentUserId]);

    // Add highlight
    const addHighlight = useCallback(
        (color) => {
            if (!selectedText) return;

            const newHighlight = {
                id: `${Date.now()}-${currentUserId}`,
                text: selectedText.text,
                page: selectedText.page,
                rects: selectedText.rects,
                color,
                userId: currentUserId,
                userName: "You",
                createdAt: new Date().toISOString(),
            };

            const newHighlights = [...highlights, newHighlight];
            setHighlights(newHighlights);
            saveHighlights(newHighlights);
            broadcastHighlights(newHighlights);
            setShowHighlightMenu(false);
            setSelectedText(null);
            window.getSelection().removeAllRanges();
        },
        [selectedText, highlights, currentUserId, saveHighlights, broadcastHighlights]
    );

    // Delete highlight
    const deleteHighlight = useCallback(
        (id) => {
            const newHighlights = highlights.filter((h) => h.id !== id);
            setHighlights(newHighlights);
            saveHighlights(newHighlights);
            broadcastHighlights(newHighlights);
        },
        [highlights, saveHighlights, broadcastHighlights]
    );

    // Remove PDF
    const removePdf = useCallback(async () => {
        if (isReadOnly || isRemoving) return;

        setIsRemoving(true);
        try {
            const res = await fetch(`/sessions/${sessionId}/pdf`, {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                },
            });
            if (res.ok) {
                setPdfUrl(null);
                setHighlights([]);
                setNumPages(null);
                setCurrentPage(1);
                setShowRemoveConfirm(false);
                onPdfChange?.(null);

                // Broadcast to partner
                if (echoChannelRef.current) {
                    echoChannelRef.current.whisper("pdf-changed", {
                        user_id: currentUserId,
                        pdf_url: null,
                        highlights: [],
                    });
                }
            }
        } catch (e) {
            console.error("Failed to remove PDF:", e);
        } finally {
            setIsRemoving(false);
        }
    }, [sessionId, csrfToken, isReadOnly, isRemoving, onPdfChange, currentUserId]);

    const onDocumentLoadSuccess = ({ numPages }) => {
        setNumPages(numPages);
    };

    const goToPrevPage = () => setCurrentPage((p) => Math.max(1, p - 1));
    const goToNextPage = () => setCurrentPage((p) => Math.min(numPages || 1, p + 1));
    const zoomIn = () => setScale((s) => Math.min(2.0, s + 0.1));
    const zoomOut = () => setScale((s) => Math.max(0.5, s - 0.1));

    if (!pdfUrl) {
        return (
            <div
                style={{
                    height: "100%",
                    display: "flex",
                    alignItems: "center",
                    justifyContent: "center",
                    color: "#6b7280",
                    flexDirection: "column",
                    gap: "12px",
                }}
            >
                <svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>No PDF loaded</span>
            </div>
        );
    }

    return (
        <div style={{ height: "100%", display: "flex", flexDirection: "column" }}>
            {/* Toolbar */}
            <div
                style={{
                    display: "flex",
                    alignItems: "center",
                    justifyContent: "space-between",
                    padding: "8px 12px",
                    borderBottom: "1px solid #e5e7eb",
                    background: "#f9fafb",
                }}
            >
                <div style={{ display: "flex", alignItems: "center", gap: "8px" }}>
                    <button
                        onClick={goToPrevPage}
                        disabled={currentPage <= 1}
                        style={{
                            padding: "4px 8px",
                            border: "1px solid #d1d5db",
                            borderRadius: "4px",
                            background: "#fff",
                            cursor: currentPage <= 1 ? "not-allowed" : "pointer",
                            opacity: currentPage <= 1 ? 0.5 : 1,
                        }}
                    >
                        Prev
                    </button>
                    <span style={{ fontSize: "14px" }}>
                        {currentPage} / {numPages || "?"}
                    </span>
                    <button
                        onClick={goToNextPage}
                        disabled={currentPage >= numPages}
                        style={{
                            padding: "4px 8px",
                            border: "1px solid #d1d5db",
                            borderRadius: "4px",
                            background: "#fff",
                            cursor: currentPage >= numPages ? "not-allowed" : "pointer",
                            opacity: currentPage >= numPages ? 0.5 : 1,
                        }}
                    >
                        Next
                    </button>
                </div>

                <div style={{ display: "flex", alignItems: "center", gap: "8px" }}>
                    <button onClick={zoomOut} style={zoomBtnStyle}>
                        -
                    </button>
                    <span style={{ fontSize: "14px", minWidth: "50px", textAlign: "center" }}>
                        {Math.round(scale * 100)}%
                    </span>
                    <button onClick={zoomIn} style={zoomBtnStyle}>
                        +
                    </button>
                </div>

                {/* Drawing tools */}
                {!isReadOnly && (
                    <div style={{ display: "flex", alignItems: "center", gap: "4px" }}>
                        <button
                            onClick={() => setDrawingMode(drawingMode === 'pen' ? null : 'pen')}
                            style={{
                                ...zoomBtnStyle,
                                background: drawingMode === 'pen' ? '#3b82f6' : '#fff',
                                color: drawingMode === 'pen' ? '#fff' : '#374151',
                            }}
                            title="Pen tool"
                        >
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                <path d="M12 19l7-7 3 3-7 7-3-3z" />
                                <path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z" />
                                <path d="M2 2l7.586 7.586" />
                            </svg>
                        </button>
                        <button
                            onClick={() => setDrawingMode(drawingMode === 'text' ? null : 'text')}
                            style={{
                                ...zoomBtnStyle,
                                background: drawingMode === 'text' ? '#3b82f6' : '#fff',
                                color: drawingMode === 'text' ? '#fff' : '#374151',
                            }}
                            title="Text tool"
                        >
                            T
                        </button>
                        {/* Color picker */}
                        <div style={{ display: "flex", gap: "2px" }}>
                            {['#ef4444', '#22c55e', '#3b82f6', '#f59e0b'].map((color) => (
                                <button
                                    key={color}
                                    onClick={() => setDrawingColor(color)}
                                    style={{
                                        width: "18px",
                                        height: "18px",
                                        borderRadius: "50%",
                                        background: color,
                                        border: drawingColor === color ? '2px solid #374151' : '2px solid transparent',
                                        cursor: "pointer",
                                    }}
                                />
                            ))}
                        </div>
                        {drawings.filter((d) => d.page === currentPage).length > 0 && (
                            <button
                                onClick={clearDrawings}
                                style={{
                                    padding: "4px 8px",
                                    border: "1px solid #d1d5db",
                                    borderRadius: "4px",
                                    background: "#fff",
                                    color: "#6b7280",
                                    fontSize: "11px",
                                    cursor: "pointer",
                                }}
                                title="Clear drawings on this page"
                            >
                                Clear
                            </button>
                        )}
                    </div>
                )}

                <div style={{ display: "flex", alignItems: "center", gap: "12px" }}>
                    {!isReadOnly && (
                        <>
                            {showRemoveConfirm ? (
                                <div style={{ display: "flex", alignItems: "center", gap: "4px" }}>
                                    <span style={{ fontSize: "12px", color: "#6b7280" }}>Remove?</span>
                                    <button
                                        onClick={removePdf}
                                        disabled={isRemoving}
                                        style={{
                                            padding: "2px 8px",
                                            border: "none",
                                            borderRadius: "4px",
                                            background: "#ef4444",
                                            color: "#fff",
                                            fontSize: "11px",
                                            cursor: isRemoving ? "not-allowed" : "pointer",
                                            opacity: isRemoving ? 0.5 : 1,
                                        }}
                                    >
                                        {isRemoving ? "..." : "Yes"}
                                    </button>
                                    <button
                                        onClick={() => setShowRemoveConfirm(false)}
                                        disabled={isRemoving}
                                        style={{
                                            padding: "2px 8px",
                                            border: "1px solid #d1d5db",
                                            borderRadius: "4px",
                                            background: "#fff",
                                            color: "#374151",
                                            fontSize: "11px",
                                            cursor: "pointer",
                                        }}
                                    >
                                        No
                                    </button>
                                </div>
                            ) : (
                                <button
                                    onClick={() => setShowRemoveConfirm(true)}
                                    style={{
                                        padding: "4px 12px",
                                        border: "1px solid #d1d5db",
                                        borderRadius: "4px",
                                        background: "#fff",
                                        color: "#6b7280",
                                        fontSize: "12px",
                                        cursor: "pointer",
                                    }}
                                >
                                    Remove
                                </button>
                            )}
                            <button
                                onClick={() => setIsFollowing(!isFollowing)}
                                style={{
                                    padding: "4px 12px",
                                    border: "none",
                                    borderRadius: "12px",
                                    background: isFollowing ? "#3b82f6" : "#e5e7eb",
                                    color: isFollowing ? "#fff" : "#374151",
                                    fontSize: "12px",
                                    cursor: "pointer",
                                }}
                            >
                                {isFollowing ? `Following ${partnerName}` : `Follow ${partnerName}`}
                            </button>
                        </>
                    )}
                    <span
                        style={{
                            fontSize: "12px",
                            color: partnerOnline ? "#3b82f6" : "#9ca3af",
                            display: "flex",
                            alignItems: "center",
                            gap: "4px",
                        }}
                    >
                        <span
                            style={{
                                width: "8px",
                                height: "8px",
                                borderRadius: "50%",
                                background: partnerOnline ? "#22c55e" : "#d1d5db",
                            }}
                        />
                        {partnerOnline
                            ? `${partnerName} on page ${partnerPage || "?"}`
                            : `${partnerName} offline`}
                    </span>
                </div>
            </div>

            {/* PDF Container */}
            <div
                ref={containerRef}
                style={{
                    flex: 1,
                    overflow: "auto",
                    position: "relative",
                    display: "flex",
                    justifyContent: "center",
                    padding: "16px",
                    background: "#f3f4f6",
                }}
                onMouseUp={handleMouseUp}
                onMouseMove={handleMouseMove}
                onScroll={handleScroll}
            >
                <Document file={pdfUrl} onLoadSuccess={onDocumentLoadSuccess}>
                    <div
                        ref={pageRef}
                        style={{
                            position: "relative",
                            cursor: drawingMode ? 'crosshair' : 'default',
                            userSelect: drawingMode ? 'none' : 'auto',
                        }}
                        onMouseDown={handleDrawingStart}
                        onMouseMove={handleDrawingMove}
                        onMouseUp={handleDrawingEnd}
                        onMouseLeave={handleDrawingEnd}
                    >
                        <Page pageNumber={currentPage} scale={scale} />

                        {/* Drawing overlay SVG - using viewBox 0 0 100 100 for percentage-based coordinates */}
                        <svg
                            viewBox="0 0 100 100"
                            preserveAspectRatio="none"
                            style={{
                                position: "absolute",
                                top: 0,
                                left: 0,
                                width: "100%",
                                height: "100%",
                                pointerEvents: "none",
                            }}
                        >
                            {/* Render saved drawings for current page */}
                            {drawings
                                .filter((d) => d.page === currentPage)
                                .map((drawing) =>
                                    drawing.type === 'stroke' ? (
                                        <path
                                            key={drawing.id}
                                            d={`M ${drawing.points.map((p) => `${p.x} ${p.y}`).join(' L ')}`}
                                            stroke={drawing.color}
                                            strokeWidth="0.3"
                                            fill="none"
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            vectorEffect="non-scaling-stroke"
                                            style={{
                                                opacity: drawing.userId === currentUserId ? 1 : 0.8,
                                            }}
                                        />
                                    ) : null
                                )}
                            {/* Render current stroke being drawn */}
                            {currentStroke && currentStroke.points.length > 1 && (
                                <path
                                    d={`M ${currentStroke.points.map((p) => `${p.x} ${p.y}`).join(' L ')}`}
                                    stroke={currentStroke.color}
                                    strokeWidth="0.3"
                                    fill="none"
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    vectorEffect="non-scaling-stroke"
                                />
                            )}
                            {/* Render partner's stroke being drawn in real-time */}
                            {partnerCurrentStroke && partnerCurrentStroke.page === currentPage && partnerCurrentStroke.points.length > 1 && (
                                <path
                                    d={`M ${partnerCurrentStroke.points.map((p) => `${p.x} ${p.y}`).join(' L ')}`}
                                    stroke={partnerCurrentStroke.color}
                                    strokeWidth="0.3"
                                    fill="none"
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    vectorEffect="non-scaling-stroke"
                                    style={{ opacity: 0.8 }}
                                />
                            )}
                        </svg>

                        {/* Render text annotations */}
                        {drawings
                            .filter((d) => d.page === currentPage && d.type === 'text')
                            .map((drawing) => (
                                <div
                                    key={drawing.id}
                                    style={{
                                        position: "absolute",
                                        left: `${drawing.x}%`,
                                        top: `${drawing.y}%`,
                                        color: drawing.color,
                                        fontSize: "14px",
                                        fontWeight: "500",
                                        whiteSpace: "nowrap",
                                        background: drawing.userId === currentUserId ? 'rgba(255,255,255,0.9)' : 'rgba(255,255,255,0.7)',
                                        padding: "2px 6px",
                                        borderRadius: "4px",
                                        border: `1px solid ${drawing.color}40`,
                                        cursor: drawing.userId === currentUserId && !isReadOnly ? 'pointer' : 'default',
                                    }}
                                    onClick={() => {
                                        if (drawing.userId === currentUserId && !isReadOnly) {
                                            deleteDrawing(drawing.id);
                                        }
                                    }}
                                    title={drawing.userId === currentUserId ? 'Click to delete' : ''}
                                >
                                    {drawing.text}
                                </div>
                            ))}

                        {/* Text input field */}
                        {textInput && (
                            <div
                                style={{
                                    position: "absolute",
                                    left: `${textInput.x}%`,
                                    top: `${textInput.y}%`,
                                    zIndex: 100,
                                }}
                                onMouseDown={(e) => e.stopPropagation()}
                                onClick={(e) => e.stopPropagation()}
                            >
                                <input
                                    autoFocus
                                    type="text"
                                    value={textInput.text}
                                    onChange={(e) => handleTextInputChange(e.target.value)}
                                    onKeyDown={(e) => {
                                        e.stopPropagation();
                                        if (e.key === 'Enter') {
                                            e.preventDefault();
                                            handleTextSubmit();
                                        }
                                        if (e.key === 'Escape') {
                                            e.preventDefault();
                                            setTextInput(null);
                                        }
                                    }}
                                    onBlur={() => {
                                        // Small delay to allow click events to process first
                                        setTimeout(() => handleTextSubmit(), 100);
                                    }}
                                    placeholder="Type here..."
                                    style={{
                                        padding: "4px 8px",
                                        border: `2px solid ${drawingColor}`,
                                        borderRadius: "4px",
                                        outline: "none",
                                        fontSize: "14px",
                                        minWidth: "120px",
                                        background: "#fff",
                                    }}
                                />
                            </div>
                        )}

                        {/* Partner's text input (real-time) */}
                        {partnerTextInput && partnerTextInput.page === currentPage && partnerOnline && (
                            <div
                                style={{
                                    position: "absolute",
                                    left: `${partnerTextInput.x}%`,
                                    top: `${partnerTextInput.y}%`,
                                    zIndex: 99,
                                    pointerEvents: "none",
                                }}
                            >
                                <div
                                    style={{
                                        padding: "4px 8px",
                                        border: `2px solid ${partnerTextInput.color || '#3b82f6'}`,
                                        borderRadius: "4px",
                                        fontSize: "14px",
                                        minWidth: "120px",
                                        minHeight: "24px",
                                        background: "rgba(255,255,255,0.95)",
                                        color: partnerTextInput.color || '#3b82f6',
                                    }}
                                >
                                    {partnerTextInput.text || ''}
                                    <span style={{
                                        display: 'inline-block',
                                        width: '2px',
                                        height: '14px',
                                        background: partnerTextInput.color || '#3b82f6',
                                        marginLeft: '1px',
                                        animation: 'blink 1s infinite',
                                    }} />
                                </div>
                                <span
                                    style={{
                                        position: "absolute",
                                        top: "-18px",
                                        left: "0",
                                        background: partnerTextInput.color || '#3b82f6',
                                        color: "#fff",
                                        fontSize: "10px",
                                        padding: "2px 6px",
                                        borderRadius: "4px",
                                        whiteSpace: "nowrap",
                                    }}
                                >
                                    {partnerName} typing...
                                </span>
                            </div>
                        )}

                        {/* Render highlights for current page - fixed positioning */}
                        {highlights
                            .filter((h) => h.page === currentPage)
                            .map((highlight) => (
                                <div key={highlight.id}>
                                    {highlight.rects.map((rect, i) => (
                                        <div
                                            key={i}
                                            style={{
                                                position: "absolute",
                                                top: rect.top,
                                                left: rect.left,
                                                width: rect.width,
                                                height: rect.height,
                                                background:
                                                    highlight.userId === currentUserId
                                                        ? `${highlight.color}40`
                                                        : `${highlight.color}30`,
                                                border:
                                                    highlight.userId !== currentUserId
                                                        ? `1px dashed ${highlight.color}`
                                                        : "none",
                                                pointerEvents: "none",
                                            }}
                                        />
                                    ))}
                                </div>
                            ))}

                        {/* Partner cursor - inside page div for proper positioning */}
                        {partnerCursor && partnerCursor.page === currentPage && partnerOnline && (
                            <div
                                style={{
                                    position: "absolute",
                                    top: partnerCursor.y,
                                    left: partnerCursor.x,
                                    pointerEvents: "none",
                                    zIndex: 90,
                                    transform: "translate(-2px, -2px)",
                                }}
                            >
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="#3b82f6">
                                    <path d="M5.65376 12.4563L2.41361 2.93489C2.18239 2.24173 2.87675 1.60371 3.5472 1.87904L21.2471 8.77596C21.9668 9.0703 21.9668 10.1183 21.2471 10.4126L14.0371 13.3584C13.8289 13.4431 13.6521 13.5876 13.5258 13.7743L10.1371 18.8168C9.72285 19.4239 8.8 19.1477 8.8 18.4155V13.3584C8.8 12.7954 8.39377 12.3174 7.85089 12.2097L5.65376 12.4563Z" />
                                </svg>
                                <span
                                    style={{
                                        position: "absolute",
                                        top: "16px",
                                        left: "12px",
                                        background: "#3b82f6",
                                        color: "#fff",
                                        fontSize: "10px",
                                        padding: "2px 6px",
                                        borderRadius: "4px",
                                        whiteSpace: "nowrap",
                                    }}
                                >
                                    {partnerName}
                                </span>
                            </div>
                        )}

                        {/* Partner's live text selection */}
                        {partnerSelection && partnerSelection.page === currentPage && partnerOnline && (
                            <>
                                {partnerSelection.rects.map((rect, i) => (
                                    <div
                                        key={i}
                                        style={{
                                            position: "absolute",
                                            top: rect.top,
                                            left: rect.left,
                                            width: rect.width,
                                            height: rect.height,
                                            background: "rgba(59, 130, 246, 0.3)", // Blue with transparency
                                            border: "1px solid rgba(59, 130, 246, 0.5)",
                                            pointerEvents: "none",
                                            zIndex: 80,
                                        }}
                                    />
                                ))}
                                {/* Label showing who is selecting */}
                                {partnerSelection.rects.length > 0 && (
                                    <div
                                        style={{
                                            position: "absolute",
                                            top: partnerSelection.rects[0].top - 20,
                                            left: partnerSelection.rects[0].left,
                                            background: "#3b82f6",
                                            color: "#fff",
                                            fontSize: "10px",
                                            padding: "2px 6px",
                                            borderRadius: "4px",
                                            whiteSpace: "nowrap",
                                            pointerEvents: "none",
                                            zIndex: 81,
                                        }}
                                    >
                                        {partnerName} selecting...
                                    </div>
                                )}
                            </>
                        )}
                    </div>
                </Document>

                {/* Highlight menu */}
                {showHighlightMenu && (
                    <div
                        style={{
                            position: "absolute",
                            top: menuPosition.y,
                            left: menuPosition.x,
                            transform: "translate(-50%, -100%)",
                            background: "#fff",
                            borderRadius: "8px",
                            boxShadow: "0 4px 12px rgba(0,0,0,0.15)",
                            padding: "8px",
                            display: "flex",
                            gap: "6px",
                            zIndex: 100,
                        }}
                    >
                        {["#fbbf24", "#34d399", "#60a5fa", "#f472b6"].map((color) => (
                            <button
                                key={color}
                                onClick={() => addHighlight(color)}
                                style={{
                                    width: "24px",
                                    height: "24px",
                                    borderRadius: "50%",
                                    background: color,
                                    border: "2px solid #fff",
                                    boxShadow: "0 1px 3px rgba(0,0,0,0.2)",
                                    cursor: "pointer",
                                }}
                            />
                        ))}
                    </div>
                )}

            </div>
        </div>
    );
}

const zoomBtnStyle = {
    width: "28px",
    height: "28px",
    border: "1px solid #d1d5db",
    borderRadius: "4px",
    background: "#fff",
    cursor: "pointer",
    fontSize: "16px",
};

window.mountPdfViewer = function (containerId, props) {
    const container = document.getElementById(containerId);
    if (!container) {
        console.error("Container not found:", containerId);
        return null;
    }

    const root = createRoot(container);
    root.render(<CollaborativePdfViewer {...props} />);
    return root;
};

export default CollaborativePdfViewer;
