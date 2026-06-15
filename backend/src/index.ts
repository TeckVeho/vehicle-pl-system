import { createApp } from "./app.js";
import { startSpreadsheetRevenueScheduler } from "./lib/scheduler.js";

const app = createApp();
const PORT = process.env.PORT ?? 4000;

app.listen(PORT, () => {
  console.log(`Backend server running at http://localhost:${PORT}`);

  // スプレッドシート売上の定期同期スケジューラを起動
  startSpreadsheetRevenueScheduler();
});
