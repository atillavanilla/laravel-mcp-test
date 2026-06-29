<x-mcp::app :title="$title">
    <x-slot:head>
        <style>
            :root {
                color-scheme: light dark;
                --apari-bg: color-mix(in srgb, Canvas 94%, CanvasText 6%);
                --apari-panel: color-mix(in srgb, Canvas 97%, CanvasText 3%);
                --apari-muted: color-mix(in srgb, CanvasText 58%, Canvas 42%);
                --apari-line: color-mix(in srgb, CanvasText 14%, Canvas 86%);
                --apari-primary: #2563eb;
                --apari-primary-strong: #1d4ed8;
                --apari-success: #059669;
                --apari-danger: #dc2626;
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                background: var(--apari-bg);
                color: CanvasText;
                font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            }

            .shell {
                min-height: 100vh;
                padding: 24px;
            }

            .layout {
                display: grid;
                grid-template-columns: minmax(0, 1fr) 300px;
                gap: 16px;
                max-width: 1040px;
                margin: 0 auto;
            }

            .header {
                grid-column: 1 / -1;
                display: flex;
                align-items: end;
                justify-content: space-between;
                gap: 16px;
                padding-bottom: 4px;
            }

            .eyebrow {
                margin: 0 0 6px;
                color: var(--apari-muted);
                font-size: 12px;
                font-weight: 700;
                letter-spacing: 0;
                text-transform: uppercase;
            }

            h1 {
                margin: 0;
                font-size: 28px;
                line-height: 1.1;
                letter-spacing: 0;
            }

            .status {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                min-height: 34px;
                padding: 0 12px;
                border: 1px solid var(--apari-line);
                border-radius: 999px;
                background: var(--apari-panel);
                color: var(--apari-muted);
                font-size: 13px;
                white-space: nowrap;
            }

            .dot {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background: var(--apari-success);
            }

            .panel,
            .sidebar {
                border: 1px solid var(--apari-line);
                border-radius: 8px;
                background: var(--apari-panel);
            }

            .panel {
                padding: 18px;
            }

            .sidebar {
                padding: 16px;
            }

            .section + .section {
                margin-top: 20px;
                padding-top: 18px;
                border-top: 1px solid var(--apari-line);
            }

            h2 {
                margin: 0 0 12px;
                font-size: 15px;
                line-height: 1.3;
                letter-spacing: 0;
            }

            .field {
                display: grid;
                gap: 7px;
                margin-bottom: 12px;
            }

            label {
                color: var(--apari-muted);
                font-size: 13px;
                font-weight: 650;
            }

            input,
            textarea {
                width: 100%;
                border: 1px solid var(--apari-line);
                border-radius: 7px;
                background: Canvas;
                color: CanvasText;
                font: inherit;
                font-size: 14px;
                line-height: 1.45;
                outline: none;
                transition: border-color 140ms ease, box-shadow 140ms ease;
            }

            input {
                min-height: 40px;
                padding: 9px 11px;
            }

            textarea {
                min-height: 112px;
                resize: vertical;
                padding: 10px 11px;
            }

            input:focus,
            textarea:focus {
                border-color: var(--apari-primary);
                box-shadow: 0 0 0 3px color-mix(in srgb, var(--apari-primary) 18%, transparent);
            }

            .actions {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
            }

            button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 38px;
                padding: 0 13px;
                border: 1px solid var(--apari-line);
                border-radius: 7px;
                background: Canvas;
                color: CanvasText;
                font: inherit;
                font-size: 14px;
                font-weight: 700;
                cursor: pointer;
            }

            button.primary {
                border-color: var(--apari-primary);
                background: var(--apari-primary);
                color: white;
            }

            button:hover {
                border-color: color-mix(in srgb, var(--apari-primary) 60%, var(--apari-line));
            }

            button.primary:hover {
                background: var(--apari-primary-strong);
            }

            button:disabled {
                cursor: not-allowed;
                opacity: 0.58;
            }

            .output {
                min-height: 244px;
                margin: 0;
                padding: 14px;
                overflow: auto;
                border: 1px solid var(--apari-line);
                border-radius: 7px;
                background: Canvas;
                color: CanvasText;
                font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
                font-size: 13px;
                line-height: 1.55;
                white-space: pre-wrap;
            }

            .hint {
                margin: 0;
                color: var(--apari-muted);
                font-size: 13px;
                line-height: 1.5;
            }

            .metric {
                display: flex;
                justify-content: space-between;
                gap: 12px;
                padding: 10px 0;
                border-bottom: 1px solid var(--apari-line);
                font-size: 13px;
            }

            .metric:last-child {
                border-bottom: 0;
            }

            .metric span:first-child {
                color: var(--apari-muted);
            }

            .metric span:last-child {
                font-weight: 750;
                text-align: right;
            }

            @media (max-width: 780px) {
                .shell {
                    padding: 16px;
                }

                .layout {
                    grid-template-columns: 1fr;
                }

                .header {
                    align-items: flex-start;
                    flex-direction: column;
                }

                .status {
                    white-space: normal;
                }
            }
        </style>

        <script type="module">
            createMcpApp(async (app) => {
                const state = {
                    tools: [],
                    lastAction: 'Ready',
                };

                const output = document.getElementById('output');
                const status = document.getElementById('status-text');
                const toolCount = document.getElementById('tool-count');
                const lastAction = document.getElementById('last-action');
                const buttons = Array.from(document.querySelectorAll('button[data-action]'));

                const setBusy = (busy) => {
                    buttons.forEach((button) => {
                        button.disabled = busy;
                    });
                };

                const writeOutput = (value) => {
                    output.textContent = value || 'No response content.';
                    app.resize();
                };

                const textFromResult = (result) => {
                    return result?.content?.map((item) => item.text ?? '').filter(Boolean).join("\n\n") ?? '';
                };

                const findTool = (...names) => {
                    const available = new Set(state.tools.map((tool) => tool.name));

                    return names.find((name) => available.has(name)) ?? names[0];
                };

                const callTool = async (name, args) => {
                    setBusy(true);
                    status.textContent = 'Working...';

                    try {
                        const result = await app.callServerTool(name, args);
                        writeOutput(textFromResult(result));
                        state.lastAction = name;
                        lastAction.textContent = name;
                        status.textContent = 'Connected';
                    } catch (error) {
                        output.textContent = error instanceof Error ? error.message : 'The tool call failed.';
                        status.textContent = 'Needs attention';
                    } finally {
                        setBusy(false);
                    }
                };

                try {
                    const list = await app.listTools();
                    state.tools = list?.tools ?? [];
                    toolCount.textContent = String(state.tools.length);
                } catch {
                    toolCount.textContent = 'Unknown';
                }

                status.textContent = 'Connected';

                document.getElementById('search-form').addEventListener('submit', async (event) => {
                    event.preventDefault();

                    const query = document.getElementById('search-query').value.trim() || 'all';

                    await callTool(findTool('search-posts-tool', 'search-posts'), { query });
                });

                document.getElementById('create-form').addEventListener('submit', async (event) => {
                    event.preventDefault();

                    const title = document.getElementById('post-title').value.trim();
                    const content = document.getElementById('post-content').value.trim();

                    if (!title || !content) {
                        writeOutput('Add a title and content before creating a post.');
                        return;
                    }

                    await callTool(findTool('create-posts-tool', 'create-post'), { title, content });
                });

                document.getElementById('clear-output').addEventListener('click', () => {
                    writeOutput('Cleared. Search posts or create a new one to see results here.');
                    state.lastAction = 'Cleared';
                    lastAction.textContent = state.lastAction;
                });

                app.autoResize();
            });
        </script>
    </x-slot:head>

    <main class="shell">
        <div class="layout">
            <header class="header">
                <div>
                    <p class="eyebrow">Apari Manager</p>
                    <h1>{{ $title }}</h1>
                </div>

                <div class="status" aria-live="polite">
                    <span class="dot" aria-hidden="true"></span>
                    <span id="status-text">Connecting...</span>
                </div>
            </header>

            <section class="panel" aria-label="Post tools">
                <div class="section">
                    <h2>Find Posts</h2>
                    <form id="search-form">
                        <div class="field">
                            <label for="search-query">Search query</label>
                            <input id="search-query" name="query" type="search" placeholder="Search title, content, or type all">
                        </div>

                        <div class="actions">
                            <button class="primary" data-action="search" type="submit">Search</button>
                            <button data-action="clear" id="clear-output" type="button">Clear</button>
                        </div>
                    </form>
                </div>

                <div class="section">
                    <h2>Create Post</h2>
                    <form id="create-form">
                        <div class="field">
                            <label for="post-title">Title</label>
                            <input id="post-title" name="title" type="text" placeholder="A clear title">
                        </div>

                        <div class="field">
                            <label for="post-content">Content</label>
                            <textarea id="post-content" name="content" placeholder="Write the post content"></textarea>
                        </div>

                        <div class="actions">
                            <button class="primary" data-action="create" type="submit">Create Post</button>
                        </div>
                    </form>
                </div>
            </section>

            <aside class="sidebar" aria-label="App summary">
                <h2>Workspace</h2>

                <div class="metric">
                    <span>Connection</span>
                    <span>Host MCP</span>
                </div>

                <div class="metric">
                    <span>Tools</span>
                    <span id="tool-count">...</span>
                </div>

                <div class="metric">
                    <span>Last action</span>
                    <span id="last-action">Ready</span>
                </div>

                <p class="hint">Use this panel to quickly search your posts or draft a new one through the MCP server.</p>
            </aside>

            <section class="panel" aria-label="Tool output">
                <h2>Result</h2>
                <pre class="output" id="output">Ready. Search posts or create a new one to see results here.</pre>
            </section>
        </div>
    </main>
</x-mcp::app>
