<aside id="rightSidebar" class="right-sidebar" data-collapsed="false">

    <!-- Toggle -->
    <button id="sbToggle" class="sb-toggle" title="Toggle sidebar">
        <i class="fas fa-chevron-right"></i>
    </button>

    <!-- ── Icon strip (always visible) ── -->
    <div class="sb-icon-strip">
        <!-- Friends -->
        <button class="sb-tab-icon active" data-tab="friends" title="Friends">
            <i class="fas fa-user-friends"></i>
            <span class="sb-badge hidden" id="pendingBadgeIcon">0</span>
        </button>

        <div class="sb-strip-divider"></div>

        <!-- Activity -->
        <button class="sb-tab-icon" data-tab="activity" title="Activity">
            <i class="fas fa-bell"></i>
        </button>

        <!-- Leaderboard -->
        <button class="sb-tab-icon" data-tab="leaderboard" title="Leaderboard">
            <i class="fas fa-trophy"></i>
        </button>
    </div>

    <!-- ── Full panel ── -->
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

        <!-- ── Friends pane ── -->
        <div class="sb-pane active" id="pane-friends">

            <div class="sb-pane-header">
                <span class="text-white text-xs font-semibold">Friends</span>
                <span class="online-badge" id="onlineCountBadge">0 online</span>
            </div>

            <!-- Search -->
            <div class="sb-search-wrap">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-600" style="font-size:10px;"></i>
                    <input id="friendSearch" type="text" placeholder="Search users to add…" class="sb-search">
                </div>
                <div id="searchResults" class="search-dropdown hidden"></div>
            </div>

            <!-- Pending requests -->
            <div id="pendingSection" class="pending-section hidden">
                <div class="sb-section-label">
                    <i class="fas fa-user-clock" style="color:#fbbf24;"></i>
                    Requests <span id="pendingCount" class="sb-count">0</span>
                </div>
                <div id="pendingList" class="friends-list" style="flex:none;max-height:140px;"></div>
            </div>

            <!-- Friends list -->
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

        </div>

        <!-- ── Activity pane ── -->
        <div class="sb-pane" id="pane-activity">
            <div class="sb-coming-soon">
                <i class="fas fa-bell"></i>
                <p style="color:#334155;">Activity feed<br>coming soon</p>
            </div>
        </div>

        <!-- ── Leaderboard pane ── -->
        <div class="sb-pane" id="pane-leaderboard">
            <div class="sb-coming-soon">
                <i class="fas fa-trophy"></i>
                <p style="color:#334155;">Leaderboard<br>coming soon</p>
            </div>
        </div>

    </div>
</aside>
