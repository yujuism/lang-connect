import React, { useState, useEffect, useCallback, useRef } from "react";
import { createRoot } from "react-dom/client";
import { Tldraw } from "@tldraw/tldraw";

// Debounce utility (for saving)
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

// Throttle utility (for live sync - sends at regular intervals)
function throttle(func, limit) {
    let inThrottle;
    let lastArgs;
    return function executedFunction(...args) {
        lastArgs = args;
        if (!inThrottle) {
            func(...args);
            inThrottle = true;
            setTimeout(() => {
                inThrottle = false;
                if (lastArgs) {
                    func(...lastArgs);
                }
            }, limit);
        }
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
    const [partnerOnline, setPartnerOnline] = useState(false);
    const [initialDocument, setInitialDocument] = useState(null);
    const echoChannelRef = useRef(null);
    const lastBroadcastRef = useRef(null);
    const lastDocumentRef = useRef(null);
    const isLoadingRef = useRef(false);
    const partnerLastSeenRef = useRef(null);

    // Parse initial data on mount
    useEffect(() => {
        if (initialData) {
            try {
                const parsed =
                    typeof initialData === "string"
                        ? JSON.parse(initialData)
                        : initialData;

                if (parsed?.snapshot) {
                    setInitialDocument(parsed.snapshot);
                }
            } catch (e) {
                console.warn("Could not parse initial canvas data:", e.message);
            }
        }
    }, [initialData]);

    // Save canvas to backend
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

    // Use refs to maintain stable debounced/throttled functions
    const saveToServerRef = useRef(saveToServer);
    saveToServerRef.current = saveToServer;

    const debouncedSaveRef = useRef(
        debounce((doc) => saveToServerRef.current(doc), 2000)
    );
    const debouncedSave = debouncedSaveRef.current;

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

    // Use throttle for live sync (100ms interval)
    const broadcastToPartnerRef = useRef(broadcastToPartner);
    broadcastToPartnerRef.current = broadcastToPartner;

    const throttledBroadcastRef = useRef(
        throttle((doc) => broadcastToPartnerRef.current(doc), 100)
    );
    const throttledBroadcast = throttledBroadcastRef.current;

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
            partnerLastSeenRef.current = Date.now();
            setPartnerOnline(true);

            // If following, move viewport to partner's position
            if (isFollowing && app && event.viewport) {
                app.setCamera(event.viewport.point, event.viewport.zoom);
            }
        });

        // Listen for partner presence heartbeat
        channel.listenForWhisper("presence", (event) => {
            if (event.user_id === currentUserId) return;
            partnerLastSeenRef.current = Date.now();
            setPartnerOnline(true);
        });

        // Send presence heartbeat every 3 seconds
        const heartbeatInterval = setInterval(() => {
            channel.whisper("presence", { user_id: currentUserId });
        }, 3000);

        // Send initial presence
        channel.whisper("presence", { user_id: currentUserId });

        // Check partner online status every 5 seconds
        const onlineCheckInterval = setInterval(() => {
            if (partnerLastSeenRef.current) {
                const timeSinceLastSeen = Date.now() - partnerLastSeenRef.current;
                setPartnerOnline(timeSinceLastSeen < 10000); // 10 seconds timeout
            }
        }, 5000);

        return () => {
            clearInterval(heartbeatInterval);
            clearInterval(onlineCheckInterval);
            window.Echo.leave(`session.${sessionId}`);
        };
    }, [sessionId, currentUserId, app, isReadOnly, isFollowing]);

    // Handle app mount - tldraw v1 callback
    const handleMount = useCallback((appInstance) => {
        setApp(appInstance);
    }, []);

    // Handle document change - tldraw v1 callback (fires on every change for live sync)
    const handleChange = useCallback(
        (appInstance, reason) => {
            if (isLoadingRef.current || isReadOnly) return;

            // Broadcast live to partner
            const document = appInstance.document;
            throttledBroadcast(document);
        },
        [isReadOnly, throttledBroadcast]
    );

    // Handle persist - tldraw v1 callback for saving
    const handlePersist = useCallback(
        (appInstance) => {
            if (isLoadingRef.current || isReadOnly) return;

            const document = appInstance.document;
            const serialized = JSON.stringify(document);

            if (serialized === lastDocumentRef.current) return;
            lastDocumentRef.current = serialized;

            debouncedSave(document);
            throttledBroadcast(document);
        },
        [debouncedSave, throttledBroadcast, isReadOnly]
    );

    // Handle presence change for cursor broadcasting
    const handleChangePresence = useCallback(
        (appInstance, presence) => {
            if (isReadOnly || !echoChannelRef.current) return;

            try {
                const camera = appInstance.getPageState().camera;
                const pointer = presence?.point;

                if (pointer) {
                    echoChannelRef.current.whisper("cursor", {
                        user_id: currentUserId,
                        cursor: { x: pointer[0], y: pointer[1] },
                        viewport: { point: camera.point, zoom: camera.zoom },
                    });
                }
            } catch (e) {
                // Ignore errors
            }
        },
        [currentUserId, isReadOnly]
    );

    // Direct pointer tracking for cursor sync
    const handlePointerMove = useCallback(
        (e) => {
            if (isReadOnly || !echoChannelRef.current || !app) return;

            try {
                const container = e.currentTarget;
                const rect = container.getBoundingClientRect();
                const camera = app.getPageState().camera;

                // Convert screen position to canvas position
                const x = (e.clientX - rect.left) / camera.zoom - camera.point[0];
                const y = (e.clientY - rect.top) / camera.zoom - camera.point[1];

                echoChannelRef.current.whisper("cursor", {
                    user_id: currentUserId,
                    cursor: { x, y },
                    viewport: { point: camera.point, zoom: camera.zoom },
                });
            } catch (e) {
                // Ignore errors
            }
        },
        [app, currentUserId, isReadOnly]
    );

    // Throttled pointer move handler
    const handlePointerMoveRef = useRef(handlePointerMove);
    handlePointerMoveRef.current = handlePointerMove;

    const throttledPointerMoveRef = useRef(
        throttle((e) => handlePointerMoveRef.current(e), 50)
    );
    const throttledPointerMove = throttledPointerMoveRef.current;

    // Toggle follow mode
    const toggleFollow = () => setIsFollowing(!isFollowing);

    // Calculate partner cursor screen position
    const getPartnerCursorScreenPos = () => {
        if (!app || !partnerCursor) return null;

        try {
            const camera = app.getPageState().camera;
            const screenX = (partnerCursor.x + camera.point[0]) * camera.zoom;
            const screenY = (partnerCursor.y + camera.point[1]) * camera.zoom;
            return { x: screenX, y: screenY };
        } catch (e) {
            return null;
        }
    };

    const cursorScreenPos = getPartnerCursorScreenPos();

    return (
        <div
            style={{ height: "100%", width: "100%", position: "relative" }}
            onPointerMove={throttledPointerMove}
        >
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
                            color: partnerOnline ? "#3b82f6" : "#9ca3af",
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
                                background: partnerOnline
                                    ? "#22c55e"
                                    : "#d1d5db",
                                animation: partnerOnline
                                    ? "pulse 2s infinite"
                                    : "none",
                            }}
                        ></span>
                        {partnerOnline
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
                {...(initialDocument ? { document: initialDocument } : {})}
                onMount={handleMount}
                onChange={handleChange}
                onPersist={handlePersist}
                onChangePresence={handleChangePresence}
                showMenu={!isReadOnly}
                showPages={false}
                showZoom={true}
                showStyles={!isReadOnly}
                showTools={!isReadOnly}
                readOnly={isReadOnly}
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
