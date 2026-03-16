import { Router } from "express";
import { requireAuth, requireRole, ROLES } from "../lib/auth.js";
import { authRouter } from "./auth.js";
import { dashboardRouter } from "./dashboard.js";
import { dailySummaryRouter } from "./daily-summary.js";
import { incomeStatementRouter } from "./income-statement.js";
import { accountItemsRouter } from "./account-items.js";
import { vehiclesRouter } from "./vehicles.js";
import { locationsRouter } from "./locations.js";
import { importRouter } from "./import.js";
import { coursesRouter } from "./courses.js";
import { usersRouter } from "./users.js";
import { syncLogsRouter } from "./sync-logs.js";

export const apiRouter = Router();

// 認証: /auth/login, /auth/logout 以外は認証必須
apiRouter.use((req, res, next) => {
  if (req.path === "/auth/login" && req.method === "POST") return next();
  if (req.path === "/auth/logout" && req.method === "POST") return next();
  return requireAuth(req, res, next);
});

apiRouter.use("/auth", authRouter);
apiRouter.use("/users", requireRole(ROLES.USER_ADMIN), usersRouter);
apiRouter.use("/dashboard", dashboardRouter);
apiRouter.use("/daily-summary", dailySummaryRouter);
apiRouter.use("/income-statement", incomeStatementRouter);
apiRouter.use("/account-items", accountItemsRouter); // 権限チェックは account-items 内で実施
apiRouter.use("/vehicles", vehiclesRouter);
apiRouter.use("/locations", locationsRouter);
apiRouter.use("/import", requireRole(ROLES.EDIT_PL), importRouter);
apiRouter.use("/courses", coursesRouter); // 権限チェックは courses 内で実施
apiRouter.use("/sync-logs", syncLogsRouter);
