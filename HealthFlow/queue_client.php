/*
# =============================================
# Project: Hospital Queue Management System
# Authors:  [ Jusminne Alipio    ], [2410050]
            [ Angel Charm Rabino ], [2411057]
            [ Carmel Sumpay      ], [2411111]
            [Alby Avery Tolentino], [2411125]
            [ Benjie Villarin    ], [2411173]
# Date: [December 16, 2025]
# Description:
    The HealthFlow Management System is based on the web-based GUI developed in HTML,
CSS, and JavaScript. Patients (kiosk and queue display) and staff (admin and front desk)
have separate interfaces, thus making the interface straightforward and easy to use 
depending on the user roles.
# =============================================
*/

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Queue - Client</title>
<style>
:root {
    --maroon: #4b0000;
    --stat-bg: #e8d6d6;
}

* { box-sizing: border-box; }

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #ffffff;
}

/* Background cross */
body::before {
    content: "";
    position: fixed;
    left: -18%;
    top: -10%;
    width: 60%;
    height: 130%;
    background-image: url('bc.png');
    background-repeat: no-repeat;
    background-size: contain;
    background-position: left center;
    opacity: 0.08;
    pointer-events: none;
    z-index: -1;
}

/* Full-width Current Serving */
.current-serving {
    width: 100%;
    padding: 26px 40px;
    box-sizing: border-box;
}

/* Header Bar */
.header-bar {
    background: var(--maroon);
    color: white;
    padding: 14px 20px;
    font-size: 20px;
    border-radius: 10px;
    font-weight: bold;
    margin-bottom: 18px;
}

/* Main Box Full Width */
.main-box {
    background: white;
    padding: 24px;
    border-radius: 12px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
    box-shadow: 0 0 15px rgba(0,0,0,0.06);
}

/* LEFT COLUMN: Token + Serving Time */
.left-column {
    display: flex;
    flex-direction: column;
    gap: 18px;
    width: 55%;
}

h3 {
    margin: 0;
    font-size: 24px;
    font-weight: bold;
    color: #000000;
    text-align: center;
}

/* Token Display */
.token-display {
    background: var(--maroon);
    color: white;
    padding: 40px;
    font-size: 72px;
    border-radius: 25px;
    text-align: center;
    font-weight: bold;
    margin: 0 40px;
}

/* Serving Time */
.small-label {
    font-size: 14px;
    margin-bottom: -6px;
    text-align: center;
    font-weight: bold;
}

.time-display {
    font-size: 32px;
    font-weight: bold;
    text-align: center;
    color: var(--maroon);
}

/* RIGHT COLUMN: Next in line */
.right-column {
    width: 30%;
    margin-right: 40px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.next-line-label {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 10px;
}

/* Next Tokens Box */
.next-box {
    background: white;
    padding: 22px;
    border-radius: 12px;
    width: 100%;
}

.next-tokens {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
}

.next-token {
    border: 3px solid var(--maroon);
    padding: 28px 20px;
    background: #fff5f5;
    border-radius: 18px;
    font-size: 24px;
    font-weight: bold;
    text-align: center;
}

/* Stats Full Width Below */
.stats-full {
    display: flex;
    gap: 20px;
    margin-top: 18px;
}

.stat-box {
    flex: 1;
    background: var(--stat-bg);
    padding: 12px;
    border-radius: 12px;
    text-align: center;
    font-size: 14px;
    font-weight: bold;
    color: var(--maroon);
}

.stat-box span {
    display: block;
    font-size: 18px;
    font-weight: bold;
    margin-top: 4px;
}

/* Mobile */
@media (max-width: 800px) {
    .main-box {
        flex-direction: column;
    }
    .left-column, .right-column {
        width: 100%;
        margin: 0;
    }
    .token-display {
        margin: 0 10px;
        font-size: 48px;
        padding: 24px;
    }
}
</style>
</head>
<body>

<div class="current-serving">
    <div class="header-bar">Current Serving</div>

    <!-- Top Columns: Token + Next in Line -->
    <div class="main-box">
        <!-- LEFT COLUMN -->
        <div class="left-column">
            <h3>TOKEN NUMBER:</h3>
            <div class="token-display" id="currentToken">--</div>

            <p class="small-label">Serving Time</p>
            <div class="time-display" id="servingTime">00:00:00</div>
        </div>

        <!-- RIGHT COLUMN -->
        <div class="right-column">
            <p class="next-line-label">Next in line:</p>
            <div class="next-box">
                <div class="next-tokens" id="nextTokens">
                    <!-- JS adds tokens -->
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Full Width Below -->
    <div class="stats-full">
        <div class="stat-box">Total Served Tokens <span id="clientServed">0</span></div>
        <div class="stat-box">In line waiting: <span id="clientWaiting">0</span></div>
    </div>
</div>

<script>
function formatDuration(ms) {
    const sec = Math.floor(ms / 1000);
    const h = String(Math.floor(sec / 3600)).padStart(2, "0");
    const m = String(Math.floor((sec % 3600) / 60)).padStart(2, "0");
    const s = String(sec % 60).padStart(2, "0");
    return `${h}:${m}:${s}`;
}

async function refresh() {
    const res = await fetch("get_status.php");
    const data = await res.json();

    const current = data.current;
    const next   = data.next || [];

    document.getElementById("currentToken").textContent = current ? current.code : "--";
    document.getElementById("clientWaiting").textContent = data.waiting_count;
    document.getElementById("clientServed").textContent  = data.served_count;

    const box = document.getElementById("nextTokens");
    box.innerHTML = "";
    if (next.length === 0) {
        const div = document.createElement("div");
        div.className = "next-token";
        div.textContent = "None";
        box.appendChild(div);
    } else {
        next.forEach(t => {
            const div = document.createElement("div");
            div.className = "next-token";
            div.textContent = t.code;
            box.appendChild(div);
        });
    }

    const label = document.getElementById("servingTime");
    if (!current || !current.started_at) {
        label.textContent = "00:00:00";
    } else {
        const diff = Date.now() - current.started_at * 1000;
        label.textContent = formatDuration(diff);
    }
}

refresh();
setInterval(refresh, 2000);
</script>

</body>
</html>
