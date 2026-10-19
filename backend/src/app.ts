import express from "express";
import cors from "cors";
import cookieParser from "cookie-parser";
import { apiRouter } from "./routes/index.js";

export function createApp(): express.Application {
  const app = express();
  const CORS_ORIGIN = process.env.CORS_ORIGIN ?? "http://localhost:3000";

  app.use(
    cors({
      origin: CORS_ORIGIN,
      credentials: true,
    })
  );
  app.use(cookieParser());
  app.use(express.json({ limit: "5mb" }));
  // Express 5 leaves `req.body` undefined when no parser ran. Keep the Express 4
  // empty-object default so handlers can destructure a missing body.
  app.use((req, _res, next) => {
    if (req.body == null) req.body = {};
    next();
  });

  app.get("/api/health", (_req, res) => {
    res.json({ ok: true });
  });

  app.use("/api", apiRouter);
  return app;
}
