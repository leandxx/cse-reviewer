<div id="sbOverlay"></div>
<aside id="leftSidebar" class="left-sidebar" data-collapsed="false" style="position:fixed;top:65px;left:0;right:auto;height:calc(100vh - 65px);width:280px;display:flex;flex-direction:row-reverse;z-index:30;">

    <!-- Toggle -->
    <button id="sbToggle" class="sb-toggle" title="Toggle sidebar">
        <i class="fas fa-chevron-right"></i>
    </button>

    <!-- ── Full panel (left part, hidden when collapsed) ── -->
    <div class="sb-panel">

        <!-- Tab bar -->
        <div class="sb-tab-bar">
            <button class="sb-tab-btn active" data-tab="friends">
                <i class="fas fa-user-friends"></i> Friends
                <span class="tab-badge hidden" id="pendingBadgeTab">0</span>
            </button>
            <button class="sb-tab-btn" data-tab="activity">
                <i class="fas fa-bell"></i> Activity
            </button>
            <button class="sb-tab-btn" data-tab="leaderboard">
                <i class="fas fa-trophy"></i> Board
            </button>
        </div>

        <!-- Friends pane -->
        <div class="sb-pane active" id="pane-friends">
            <div class="sb-pane-header">
                <span class="text-white text-xs font-semibold">Friends</span>
                <span class="online-badge" id="onlineCountBadge">0 online</span>
            </div>
            <div class="sb-search-wrap">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-600" style="font-size:10px;"></i>
                    <input id="friendSearch" type="text" placeholder="Search users to add…" class="sb-search">
                </div>
                <div id="searchResults" class="search-dropdown hidden"></div>
            </div>
            <div id="pendingSection" class="pending-section hidden">
                <div class="sb-section-label">
                    <i class="fas fa-user-clock" style="color:#fbbf24;"></i>
                    Requests <span id="pendingCount" class="sb-count">0</span>
                </div>
                <div id="pendingList" class="friends-list" style="flex:none;max-height:140px;"></div>
            </div>
            <div class="sb-section-label">
                <i class="fas fa-users" style="color:#818cf8;"></i>
                Friends <span id="friendCount" class="sb-count">0</span>
            </div>
            <div id="friendsList" class="friends-list">
                <div class="sb-empty" id="friendsEmpty">
                    <i class="fas fa-user-plus" style="font-size:22px;margin-bottom:6px;"></i>
                    <p>No friends yet.<br>Search to add someone!</p>
                </div>
            </div>

            <!-- Suggestions -->
            <div class="sb-section-label" style="margin-top:4px;">
                <i class="fas fa-user-plus" style="color:#34d399;"></i>
                People You May Know <span id="suggestCount" class="sb-count">0</span>
            </div>
            <div id="suggestionsList" class="friends-list">
                <div class="sb-empty" id="suggestionsEmpty">
                    <p>No suggestions available.</p>
                </div>
            </div>
        </div>

        <!-- Activity pane -->
        <div class="sb-pane" id="pane-activity">
            <div class="sb-coming-soon">
                <i class="fas fa-bell"></i>
                <p style="color:#334155;">Activity feed<br>coming soon</p>
            </div>
        </div>

        <!-- Leaderboard pane -->
        <div class="sb-pane" id="pane-leaderboard">
            <div class="sb-pane-header">
                <span class="text-white text-xs font-semibold">Your Progress</span>
            </div>
            <!-- XP card -->
            <div style="padding:10px 12px 6px;flex-shrink:0;">
                <div style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.07);border-radius:12px;padding:12px;">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                        <div style="width:32px;height:32px;border-radius:9px;background:linear-gradient(135deg,#f59e0b,#f97316);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="fas fa-star" style="color:#fff;font-size:13px;"></i>
                        </div>
                        <div>
                            <div style="color:#fff;font-size:13px;font-weight:700;">Level <span id="sb-level">—</span></div>
                            <div style="color:#64748b;font-size:10px;"><span id="sb-xp">—</span> XP total</div>
                        </div>
                    </div>
                    <div style="height:6px;background:#1e293b;border-radius:99px;overflow:hidden;">
                        <div id="sb-xp-bar" style="height:100%;background:linear-gradient(90deg,#f59e0b,#f97316);border-radius:99px;width:0%;transition:width 0.6s;"></div>
                    </div>
                    <div style="color:#64748b;font-size:10px;margin-top:4px;"><span id="sb-xp-next">—</span> / 100 XP to next level</div>
                </div>
            </div>
            <!-- Recent sessions -->
            <div class="sb-section-label" style="margin-top:4px;">
                <i class="fas fa-history" style="color:#818cf8;"></i>
                Recent Sessions
            </div>
            <div id="sb-history" class="friends-list" style="padding:0 8px 8px;">
                <div class="sb-empty"><i class="fas fa-spinner fa-spin" style="font-size:16px;margin-bottom:4px;"></i></div>
            </div>
        </div>

    </div>

    <!-- ── Icon strip (always visible, flush to right edge) ── -->
    <div class="sb-icon-strip">
        <button class="sb-tab-icon active" data-tab="friends" title="Friends">
            <i class="fas fa-user-friends"></i>
            <span class="sb-badge hidden" id="pendingBadgeIcon">0</span>
        </button>
        <div class="sb-strip-divider"></div>
        <button class="sb-tab-icon" data-tab="activity" title="Activity">
            <i class="fas fa-bell"></i>
        </button>
        <button class="sb-tab-icon" data-tab="leaderboard" title="Leaderboard">
            <i class="fas fa-trophy"></i>
        </button>
    </div>

</aside>
