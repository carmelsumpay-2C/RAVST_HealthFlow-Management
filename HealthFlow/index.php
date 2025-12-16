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
<title>HealthFlow Management System - Kiosk</title>

<style>
:root {
    --maroon: #4b0000;
    --page-bg: #333333;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background: var(--page-bg);
    font-family: Arial, sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}

/* Main frame */
.screen-frame {
    width: 96vw;
    max-width: 1100px;
    height: 90vh;
    max-height: 650px;
    background: white;
    position: relative;
    box-shadow: 0 0 18px rgba(0,0,0,0.6);
    overflow: hidden;
}

/* Left Background FIXED */
.bg-left {
    position: absolute;
    top: 50%;
    left: 0;
    transform: translateY(-50%);
    height: 140%;
    width: auto;
    opacity: 1;
    pointer-events: none;
    z-index: 0;
}

/* Header */
.kiosk-header {
    background: var(--maroon);
    color: white;
    padding: 14px 30px;
    font-size: 23px;
    font-weight: bold;
    border-radius: 14px;
    margin: 20px 30px 0 30px;
    position: relative;
    z-index: 2;
}

/* Content */
.kiosk-content {
    padding: 35px 30px 30px 30px;
    text-align: center;
    position: relative;
    z-index: 2;
}

/* Logo FIXED */
.plus-logo {
    width: 55px;
    height: 55px;
    object-fit: contain;
    margin-bottom: 12px;
}

/* Text */
.welcome-title {
    font-size: 20px;
    font-weight: bold;
    margin-bottom: 4px;
}

.welcome-subtitle {
    margin-bottom: 28px;
    font-size: 15px;
}

/* Button Grid */
.button-grid {
    width: 75%;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    column-gap: 60px;
    row-gap: 28px;
}

/* Buttons */
.service-btn {
    width: 100%;
    height: 100px;
    border: 4px solid var(--maroon);
    border-radius: 22px;
    background: #fff;
    font-size: 19px;
    font-weight: bold;
    letter-spacing: 0.5px;
    cursor: pointer;
    transition: 0.15s;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 5px;
    line-height: 1.2;
}

.service-btn:hover {
    background: var(--maroon);
    color: white;
    transform: translateY(-2px);
}

/* Mobile */
@media (max-width: 700px) {
    .button-grid {
        grid-template-columns: 1fr;
        width: 90%;
        column-gap: 0;
    }
}
</style>
</head>
<body>

<div class="screen-frame">

    <!-- Background FIXED -->
    <img src="background_left.png" class="bg-left" alt="">

    <!-- Header -->
    <div class="kiosk-header">HealthFlow Management System</div>

    <!-- Content -->
    <div class="kiosk-content">
        <img src="logo.png" class="plus-logo" alt="+">

        <div class="welcome-title">WELCOME!</div>
        <div class="welcome-subtitle">Please choose the service you need:</div>

        <!-- BUTTON GRID -->
        <div class="button-grid">
            <button class="service-btn" onclick="selectService('CHECK-UP')">CHECK-UP</button>
            <button class="service-btn" onclick="selectService('FIRST AID')">FIRST AID</button>
            <button class="service-btn" onclick="selectService('REQUEST MEDICINE')">REQUEST<br>MEDICINE</button>
            <button class="service-btn" onclick="selectService('EMERGENCY ASSISTANCE')">EMERGENCY<br>ASSISTANCE</button>
        </div>
    </div>

</div>

<script>
async function selectService(service) {
    const fd = new FormData();
    fd.append("service", service);

    const res = await fetch("create_token.php", {
        method: "POST",
        body: fd
    });

    const data = await res.json();

    if (!data.success) {
        alert("Error: " + data.message);
        return;
    }

    alert("Your token is: " + data.code + "\nService: " + data.service);
}
</script>

</body>
</html>
