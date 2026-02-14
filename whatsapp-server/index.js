const { Client, LocalAuth } = require("whatsapp-web.js");
const qrcode = require("qrcode-terminal");
const express = require("express");
const bodyParser = require("body-parser");
// Initialize Express App
const app = express();
const port = 3000;

app.use(bodyParser.json());

// Initialize WhatsApp Client
const client = new Client({
    authStrategy: new LocalAuth(),
    puppeteer: {
        headless: true, // Run in background (no browser window)
        args: ["--no-sandbox", "--disable-setuid-sandbox"],
    },
});

let isReady = false;

// Generate QR Code
client.on("qr", (qr) => {
    console.log(
        "\n=============================================================",
    );
    console.log("SCAN THIS QR CODE WITH WHATSAPP (LINKED DEVICES):");
    console.log(
        "=============================================================\n",
    );
    qrcode.generate(qr, { small: true });
});

client.on("ready", () => {
    console.log("\n✅ WhatsApp Client is READY!");
    isReady = true;
});

client.on("auth_failure", (msg) => {
    console.error("❌ Authentication failure:", msg);
});

// API Endpoint to send messages
app.post("/send-message", async (req, res) => {
    if (!isReady) {
        return res.status(503).json({
            success: false,
            error: "Client not ready yet. Please scan QR code.",
        });
    }

    const { phone, message } = req.body;

    if (!phone || !message) {
        return res
            .status(400)
            .json({ success: false, error: "Phone and message are required." });
    }

    try {
        // Format phone number
        // WhatsApp ID format: 970591234567@c.us (No + symbol)
        let chatId = phone.replace(/[^0-9]/g, ""); // Remove + and spaces

        // If it starts with 05 (Local format), Convert to International 972
        if (chatId.startsWith("05")) {
            chatId = "972" + chatId.substring(1);
        }

        // If it starts with 5 (Missing leading zero or country code?), assume 972
        else if (chatId.startsWith("5") && chatId.length === 9) {
            chatId = "972" + chatId;
        }

        // Validate length roughly (10-15 digits)
        if (chatId.length < 10) {
            console.warn(`⚠️ Phone number ${chatId} seems too short.`);
        }

        // Append WhatsApp Suffix
        chatId = `${chatId}@c.us`;

        // Send
        const response = await client.sendMessage(chatId, message);

        console.log(`📨 Sent to ${chatId}: ${message.substring(0, 30)}...`);
        res.json({ success: true, response });
    } catch (error) {
        console.error("❌ Failed to send:", error);
        res.status(500).json({ success: false, error: error.message });
    }
});

// Start Server
app.listen(port, () => {
    console.log(`\n🚀 Server running on http://localhost:${port}`);
    console.log("⏳ Initializing WhatsApp Client...");
    client.initialize();
});
