import React, { useState, useEffect, useCallback, useRef } from "react";
import { createRoot } from "react-dom/client";
import { Tldraw } from "@tldraw/tldraw";

// Debounce utility
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function CollaborativeCanvas({
    sessionId,
    currentUserId,
    partnerName,
    partnerId,
    isReadOnly = false,
    initialData = null,
    csrfToken,
}) {
    const [app, setApp] = useState(null);
    const [saveStatus, setSaveStatus] = useState("saved");
    const [isFollowing, setIsFollowing] = useState(false);
    const [partnerCursor, setPartnerCursor] = useState(null);
    const echoChannelRef = useRef(null);
    const lastBroadcastRef = useRef(null);
    const lastDocumentRef = useRef(null);
    const isLoadingRef = useRef(false);

    // Save canvas to backend (debounced)
    const saveToServer = useCallback(
        async (document) => {
            if (isReadOnly || isLoadingRef.current) return;

            setSaveStatus("saving");

            try {
                const response = await fetch(`/sessions/${sessionId}/canvas`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                        Accept: "application/json",
                    },
                    body: JSON.stringify({ snapshot: document }),
                });

                if (response.ok) {
                    setSaveStatus("saved");
                } else {
                    setSaveStatus("error");
                }
            } catch (error) {
                console.error("Error saving canvas:", error);
                setSaveStatus("error");
            }
        },
        [sessionId, csrfToken, isReadOnly]
    );

    const debouncedSave = useCallback(debounce(saveToServer, 2000), [
        saveToServer,
    ]);

    // Broadcast changes to partner
    const broadcastToPartner = useCallback(
        (document) => {
            if (isReadOnly || !echoChannelRef.current || isLoadingRef.current)
                return;

            const serialized = JSON.stringify(document);
            if (serialized === lastBroadcastRef.current) return;
            lastBroadcastRef.current = serialized;

            fetch(`/sessions/${sessionId}/canvas/broadcast`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: JSON.stringify({ snapshot: document }),
            }).catch(console.error);
        },
        [sessionId, csrfToken, isReadOnly]
    );

    const debouncedBroadcast = useCallback(debounce(broadcastToPartner, 500), [
        broadcastToPartner,
    ]);

    // Initialize WebSocket connection
    useEffect(() => {
        if (isReadOnly || typeof window.Echo === "undefined") return;

        const channel = window.Echo.private(`session.${sessionId}`);
        echoChannelRef.current = channel;

        // Listen for canvas changes from partner
        channel.listen(".canvas.changed", (event) => {
            if (event.user_id === currentUserId || !app) return;

            if (event.snapshot) {
                try {
                    isLoadingRef.current = true;
                    app.loadDocument(event.snapshot);
                    setTimeout(() => {
                        isLoadingRef.current = false;
                    }, 100);
                } catch (e) {
                    console.error("Error loading partner document:", e);
                    isLoadingRef.current = false;
                }
            }
        });

        // Listen for partner cursor position (whisper)
        channel.listenForWhisper("cursor", (event) => {
            if (event.user_id === currentUserId) return;
            setPartnerCursor(event.cursor);

            // If following, move viewport to partner's position
            if (isFollowing && app && event.viewport) {
                app.setCamera(event.viewport);
            }
        });

        return () => {
            window.Echo.leave(`session.${sessionId}`);
        };
    }, [sessionId, currentUserId, app, isReadOnly, isFollowing]);

    // Broadcast cursor position frequently
    useEffect(() => {
        if (!app || isReadOnly || !echoChannelRef.current) return;

        let lastCursorBroadcast = 0;

        const handlePointerMove = (info) => {
            const now = Date.now();
            if (now - lastCursorBroadcast < 50) return; // Throttle to 50ms
            lastCursorBroadcast = now;

            const camera = app.getCamera();
            const pointer = app.inputs?.currentPoint;

            if (pointer) {
                echoChannelRef.current.whisper("cursor", {
                    user_id: currentUserId,
                    cursor: { x: pointer[0], y: pointer[1] },
                    viewport: camera,
                });
            }
        };

        // Subscribe to pointer move events
        app.on("pointer-move", handlePointerMove);

        return () => {
            app.off("pointer-move", handlePointerMove);
        };
    }, [app, currentUserId, isReadOnly]);

    // Subscribe to document changes for autosave and live sync
    useEffect(() => {
        if (!app) return;

        const handleChange = () => {
            if (isLoadingRef.current) return;

            const document = app.document;
            const serialized = JSON.stringify(document);

            if (serialized === lastDocumentRef.current) return;
            lastDocumentRef.current = serialized;

            debouncedSave(document);
            debouncedBroadcast(document);
        };

        // Listen to persist events (when document changes)
        app.on("persist", handleChange);

        return () => {
            app.off("persist", handleChange);
        };
    }, [app, debouncedSave, debouncedBroadcast]);

    // Handle app mount
    const handleMount = useCallback(
        (appInstance) => {
            setApp(appInstance);

            // Load initial data if available
            if (initialData) {
                try {
                    const parsed =
                        typeof initialData === "string"
                            ? JSON.parse(initialData)
                            : initialData;

                    if (parsed?.snapshot) {
                        isLoadingRef.current = true;
                        appInstance.loadDocument(parsed.snapshot);
                        setTimeout(() => {
                            isLoadingRef.current = false;
                        }, 100);
                    }
                } catch (e) {
                    console.warn(
                        "Could not load initial canvas data:",
                        e.message
                    );
                }
            }

            if (isReadOnly) {
                appInstance.readOnly = true;
            }
        },
        [initialData, isReadOnly]
    );

    // Toggle follow mode
    const toggleFollow = () => setIsFollowing(!isFollowing);

    // Calculate partner cursor screen position
    const getPartnerCursorScreenPos = () => {
        if (!app || !partnerCursor) return null;

        const camera = app.getCamera();
        const screenX = (partnerCursor.x - camera.point[0]) * camera.zoom;
        const screenY = (partnerCursor.y - camera.point[1]) * camera.zoom;

        return { x: screenX, y: screenY };
    };

    const cursorScreenPos = getPartnerCursorScreenPos();

    return (
        <div style={{ height: "100%", width: "100%", position: "relative" }}>
            {/* Control bar - positioned at top center */}
            {!isReadOnly && (
                <div
                    style={{
                        position: "absolute",
                        top: "10px",
                        left: "50%",
                        transform: "translateX(-50%)",
                        zIndex: 1000,
                        display: "flex",
                        gap: "12px",
                        alignItems: "center",
                        background: "rgba(255,255,255,0.95)",
                        padding: "8px 16px",
                        borderRadius: "24px",
                        boxShadow: "0 2px 12px rgba(0,0,0,0.15)",
                        border: "1px solid rgba(0,0,0,0.1)",
                    }}
                >
                    <button
                        onClick={toggleFollow}
                        style={{
                            padding: "6px 14px",
                            fontSize: "13px",
                            border: "none",
                            borderRadius: "16px",
                            cursor: "pointer",
                            background: isFollowing ? "#3b82f6" : "#f3f4f6",
                            color: isFollowing ? "#fff" : "#374151",
                            fontWeight: "500",
                            transition: "all 0.2s",
                        }}
                    >
                        {isFollowing
                            ? `Following ${partnerName}`
                            : `Follow ${partnerName}`}
                    </button>

                    <div
                        style={{
                            width: "1px",
                            height: "20px",
                            background: "#e5e7eb",
                        }}
                    ></div>

                    <span style={{ fontSize: "12px", color: "#666" }}>
                        {saveStatus === "saving" && "Saving..."}
                        {saveStatus === "saved" && "Saved"}
                        {saveStatus === "error" && "Error"}
                    </span>

                    <div
                        style={{
                            width: "1px",
                            height: "20px",
                            background: "#e5e7eb",
                        }}
                    ></div>

                    <span
                        style={{
                            fontSize: "12px",
                            color: partnerCursor ? "#3b82f6" : "#9ca3af",
                            display: "flex",
                            alignItems: "center",
                            gap: "6px",
                        }}
                    >
                        <span
                            style={{
                                width: "8px",
                                height: "8px",
                                borderRadius: "50%",
                                background: partnerCursor
                                    ? "#22c55e"
                                    : "#d1d5db",
                                animation: partnerCursor
                                    ? "pulse 2s infinite"
                                    : "none",
                            }}
                        ></span>
                        {partnerCursor
                            ? `${partnerName} is here`
                            : `${partnerName} offline`}
                    </span>
                </div>
            )}

            {/* Partner cursor on canvas */}
            {cursorScreenPos && !isFollowing && (
                <div
                    style={{
                        position: "absolute",
                        left: cursorScreenPos.x,
                        top: cursorScreenPos.y,
                        zIndex: 999,
                        pointerEvents: "none",
                        transform: "translate(-2px, -2px)",
                    }}
                >
                    {/* Cursor arrow */}
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path
                            d="M5.5 3.21V20.8c0 .45.54.67.85.35l4.86-4.86a.5.5 0 0 1 .35-.15h6.87c.48 0 .72-.58.38-.92L6.35 2.87a.5.5 0 0 0-.85.34Z"
                            fill="#3b82f6"
                            stroke="#fff"
                            strokeWidth="1.5"
                        />
                    </svg>
                    {/* Name label */}
                    <div
                        style={{
                            position: "absolute",
                            left: "18px",
                            top: "16px",
                            background: "#3b82f6",
                            color: "#fff",
                            padding: "2px 8px",
                            borderRadius: "4px",
                            fontSize: "11px",
                            fontWeight: "500",
                            whiteSpace: "nowrap",
                        }}
                    >
                        {partnerName}
                    </div>
                </div>
            )}

            <Tldraw
                onMount={handleMount}
                showMenu={!isReadOnly}
                showPages={false}
                showZoom={true}
                showStyles={!isReadOnly}
                showTools={!isReadOnly}
            />

            <style>{`
                @keyframes pulse {
                    0%, 100% { opacity: 1; }
                    50% { opacity: 0.5; }
                }
            `}</style>
        </div>
    );
}

window.mountTldrawCanvas = function (containerId, props) {
    const container = document.getElementById(containerId);
    if (!container) {
        console.error("Container not found:", containerId);
        return null;
    }

    const root = createRoot(container);
    root.render(<CollaborativeCanvas {...props} />);
    return root;
};

export default CollaborativeCanvas;
