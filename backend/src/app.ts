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

  app.use("/api", apiRouter);
  return app;
}
