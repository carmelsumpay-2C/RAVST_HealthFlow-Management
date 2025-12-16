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
<title>Queue - Admin Panel</title>

<style>
:root {
    --maroon: #4b0000;
    --stat-bg: #e8d6d6;
}

* { box-sizing: border-box; }

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #222222;              
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}

.frame {
    background: #ffffff;
    width: 960px;
    padding: 24px 32px;
    box-shadow: 0 0 18px rgba(0,0,0,0.45);
}

.current-serving {
    width: 100%;
    box-sizing: border-box;
}


.main-box {
    background: #ffffff;
    border-radius: 0; 
    padding: 24px;
    display: flex;
    justify-content: space-between;
    gap: 24px;
}


.left-column {
    width: 60%;
    display: flex;
    flex-direction: column;
    gap: 22px;
    text-align: center;
}

.header-bar {
    background: var(--maroon);
    color: white;
    padding: 12px 18px;
    font-size: 18px;
    border-radius: 6px;
    font-weight: bold;
    text-align: left;
}

h3 {
    margin: 0;
    font-size: 22px;
    font-weight: bold;
}

.token-display {
    background: var(--maroon);
    color: white;
    padding: 32px;
    font-size: 64px;
    border-radius: 28px;
    font-weight: bold;
}

.small-label {
    font-size: 14px;
    font-weight: bold;
    margin-bottom: -6px;
}

.time-display {
    font-size: 30px;
    font-weight: bold;
    color: var(--maroon);
}

.stats-full {
    display: flex;
    gap: 16px;
    margin-top: 8px;
}

.stat-box {
    flex: 1;
    background: var(--stat-bg);
    padding: 10px;
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


.right-column {
    width: 28%;
    display: flex;
    flex-direction: column;
    gap: 18px;
    justify-content: center;
}

.admin-btn {
    background: var(--maroon);
    color: white;
    padding: 26px 18px;
    font-size: 20px;
    border: none;
    border-radius: 18px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.15s;
}

.admin-btn:hover {
    opacity: 0.9;
    transform: translateY(-1px);
}

@media (max-width: 900px) {
    body { align-items: flex-start; padding: 15px 0; }
    .frame { width: 100%; margin: 0 8px; }
    .main-box { flex-direction: column; }
    .left-column, .right-column { width: 100%; }
}
</style>
</head>
<body>

<div class="frame">
    <div class="current-serving">
        <div class="main-box">

            <!-- LEFT SIDE -->
            <div class="left-column">
                <div class="header-bar">Current Serving</div>

                <h3>TOKEN NUMBER:</h3>
                <div class="token-display" id="adminToken">RM-0019</div>

                <p class="small-label">Serving Time</p>
                <div class="time-display" id="adminServingTime">00:15:01</div>

                <div class="stats-full">
                    <div class="stat-box">Total Served Tokens <span id="adminServed">10</span></div>
                    <div class="stat-box">In line waiting: <span id="adminWaiting">5</span></div>
                </div>
            </div>

            <!-- RIGHT SIDE BUTTONS -->
            <div class="right-column">
                <button class="admin-btn" id="btnNext">NEXT</button>
                <button class="admin-btn" id="btnCall">CALL</button>
                <button class="admin-btn" id="btnRecall">RECALL</button>
                <button class="admin-btn" id="btnClose">CLOSE</button>
            </div>

        </div>
    </div>
</div>

<script>
// Format ms -> HH:MM:SS
function formatDuration(ms) {
    const sec = Math.floor(ms / 1000);
    const h = String(Math.floor(sec / 3600)).padStart(2, "0");
    const m = String(Math.floor((sec % 3600) / 60)).padStart(2, "0");
    const s = String(sec % 60).padStart(2, "0");
    return `${h}:${m}:${s}`;
}

// Load status from backend
async function fetchStatus() {
    try {
        const res = await fetch("get_status.php");
        const data = await res.json();

        const current = data.current;
        document.getElementById("adminToken").textContent = current ? current.code : "--";
        document.getElementById("adminWaiting").textContent = data.waiting_count;
        document.getElementById("adminServed").textContent  = data.served_count;

        const label = document.getElementById("adminServingTime");
        if (!current || !current.started_at) {
            label.textContent = "00:00:00";
        } else {
            const diff = Date.now() - current.started_at * 1000;
            label.textContent = formatDuration(diff);
        }
    } catch (e) {
        console.error(e);
    }
}

// Actions
async function nextToken() {
    await fetch("next.php");
    fetchStatus();
}

async function closeCurrent() {
    await fetch("close.php");
    fetchStatus();
}

function callToken() {
    const code = document.getElementById("adminToken").textContent;
    if (code === "--") {
        alert("No token is currently being served.");
    } else {
        alert("CALLING: " + code);
    }
}

function recallToken() {
    const code = document.getElementById("adminToken").textContent;
    if (code === "--") {
        alert("No token to recall.");
    } else {
        alert("RECALLING: " + code);
    }
}

// Bind buttons
document.getElementById("btnNext").addEventListener("click", nextToken);
document.getElementById("btnClose").addEventListener("click", closeCurrent);
document.getElementById("btnCall").addEventListener("click", callToken);
document.getElementById("btnRecall").addEventListener("click", recallToken);

// Initial load + polling
fetchStatus();
setInterval(fetchStatus, 2000);
</script>

</body>
</html>
