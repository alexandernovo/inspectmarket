const { app, BrowserWindow } = require("electron");
const path = require("path");

let mainWindow;

function createWindow() {
    mainWindow = new BrowserWindow({
        width: 1000,
        height: 800,
        icon: path.join(__dirname, "assets/image/logo.png"),
        webPreferences: {
            nodeIntegration: true,
        },
    });

    const baseUrl = "http://127.0.0.1:8000";
    mainWindow.loadURL(baseUrl);

    mainWindow.setMenuBarVisibility(false);
    mainWindow.maximize();
    mainWindow.webContents.on("did-finish-load", () => {
        mainWindow.setMenuBarVisibility(false);
    });

    mainWindow.on("closed", () => {
        mainWindow = null;
    });
}

app.whenReady().then(createWindow);

app.on("window-all-closed", () => {
    if (process.platform !== "darwin") {
        app.quit();
    }
});

app.on("activate", () => {
    if (BrowserWindow.getAllWindows().length === 0) {
        createWindow();
    }
});
