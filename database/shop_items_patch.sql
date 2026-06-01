-- Run this to add new shop items (safe to re-run, uses INSERT IGNORE)
INSERT IGNORE INTO shop_items (id, type, name, value, price, description, preview_css, theme) VALUES
-- ⚡ Electric / Neon titles
(19, 'title',      '⚡ Voltage',       'Voltage',        400,  'Electrifying presence.',               'background:linear-gradient(90deg,#38bdf8,#818cf8);-webkit-background-clip:text;-webkit-text-fill-color:transparent;font-weight:900;', NULL),
(20, 'title',      '🌙 Phantom',       'Phantom',        350,  'Silent. Deadly. Unstoppable.',         'color:#c084fc;font-weight:800;text-shadow:0 0 10px rgba(192,132,252,0.6);', NULL),
(21, 'title',      '💀 Reaper',        'Reaper',         900,  'You came. You saw. You aced it.',      'background:linear-gradient(90deg,#94a3b8,#e2e8f0,#94a3b8);-webkit-background-clip:text;-webkit-text-fill-color:transparent;font-weight:900;letter-spacing:1px;', NULL),
(22, 'title',      '🏆 Champion',      'Champion',       600,  'Proven. Tested. Victorious.',          'background:linear-gradient(90deg,#fbbf24,#f59e0b,#d97706);-webkit-background-clip:text;-webkit-text-fill-color:transparent;font-weight:900;', NULL),
-- Neon name colors
(23, 'name_color', 'Neon Blue',       'color-neon-blue', 200, 'Electric neon blue.',                  'color:#38bdf8;font-weight:700;text-shadow:0 0 8px rgba(56,189,248,0.5);', NULL),
(24, 'name_color', 'Neon Pink',       'color-neon-pink', 200, 'Hot neon pink.',                       'color:#f472b6;font-weight:700;text-shadow:0 0 8px rgba(244,114,182,0.5);', NULL),
(25, 'name_color', 'Neon Green',      'color-neon-green',200, 'Matrix-style neon green.',             'color:#4ade80;font-weight:700;text-shadow:0 0 8px rgba(74,222,128,0.5);', NULL),
-- New backgrounds
(26, 'name_bg',    'Violet Glow',     'bg-violet',      250,  'Deep violet aura.',                    'background:rgba(139,92,246,0.25);border-radius:6px;padding:1px 6px;', NULL),
(27, 'name_bg',    'Rose Glow',       'bg-rose',        250,  'Soft rose highlight.',                 'background:rgba(244,63,94,0.2);border-radius:6px;padding:1px 6px;', NULL),
(28, 'name_bg',    'Sky Glow',        'bg-sky',         250,  'Clear sky blue.',                      'background:rgba(14,165,233,0.2);border-radius:6px;padding:1px 6px;', NULL),
-- 💎 Legendary tier
(29, 'title',      '💎 Diamond',       'Diamond',        2000, 'The pinnacle. Rarest of the rare.',    'background:linear-gradient(90deg,#bae6fd,#e0f2fe,#7dd3fc,#38bdf8,#bae6fd);-webkit-background-clip:text;-webkit-text-fill-color:transparent;font-weight:900;letter-spacing:1px;', NULL),
(30, 'name_bg',    '💎 Prismatic',     'bg-prismatic',   1500, 'Shifts like a prism. Ultra rare.',     'background:linear-gradient(90deg,rgba(99,102,241,0.35),rgba(168,85,247,0.35),rgba(236,72,153,0.35));border-radius:6px;padding:1px 6px;', NULL),
(31, 'title',      '🔥 Overlord',      'Overlord',       1800, 'Above all. The final boss.',           'background:linear-gradient(90deg,#fff7ed,#fbbf24,#f97316,#dc2626,#7f1d1d);-webkit-background-clip:text;-webkit-text-fill-color:transparent;font-weight:900;letter-spacing:2px;', 'fire');
