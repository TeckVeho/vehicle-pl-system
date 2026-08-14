import type { Express } from "express";
import { createApp } from "../../app.js";

let testApp: Express | null = null;

export function getTestApp(): Express {
  if (!testApp) {
    testApp = createApp();
  }
  return testApp;
}
