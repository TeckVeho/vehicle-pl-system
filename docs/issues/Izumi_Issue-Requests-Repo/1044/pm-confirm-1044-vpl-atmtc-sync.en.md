# PM confirmation — ATMTC delivery data into vehicle P&L (Issue #1044)

**For:** PM / PO / BA  
**Vietnamese:** [`pm-confirm-1044-vpl-atmtc-sync.md`](./pm-confirm-1044-vpl-atmtc-sync.md)

**Purpose:** The PM only needs to **choose business rules** (what is correct for operations and finance). **How it is implemented in software** is for the engineering team.

**Related:**  
- Detailed request: [#1044](https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/1044)  
- Broader flow (ATMTC → Cloud): [#1010](https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/1010)

---

## Short explanation — what are we doing?

1. **Delivery / trip data** from **ATMTC** is already stored in **Izumi Cloud** (one row per trip, like a trip log).  
2. **Next step:** **send it to the vehicle P&L system (VPL)** so that **who drove which vehicle on which day** and **how many “runs” each vehicle had that day** feed into **per-vehicle cost and revenue calculations**.  
3. Cloud **tries to match** each row to an **employee** and **vehicle** in Izumi (when it succeeds, the row is “matched”; when not, internal reference is missing).

**The PM does not need to pick column codes from files** — employee/vehicle matching on Cloud is handled by technical rules. This form only asks: **when updating P&L, how should the company count and treat the data to match business intent?**

---

## Part A — Recorded by the project team (no PM choice)

| # | Plain-language summary |
|---|-------------------------|
| A1 | Overall order: **ATMTC → stored on Cloud → then sync to vehicle P&L**. |
| A2 | The path into vehicle P&L will use an **administrator account** (high privilege, same class as other sensitive sync flows) — so ordinary users cannot push operational numbers. |
| A3 | When Cloud **already knows** which employee/vehicle a row is, vehicle P&L will use **that same person / vehicle**. The PM does not need to re-decide from driver codes or plates on the ATMTC file. |
| A4 | If you later want to **change matching rules** on Cloud, that is a **separate** topic (ingestion/master data). This form is only about **using data already on Cloud** to update vehicle P&L. |

---

## Part B — PM to confirm (tick [ ] → [x], or add notes under “PM comments”)

### B1 — Per vehicle, per day: how should **“run count”** on P&L be calculated?

*Example: the same vehicle has 3 trip rows on one day in ATMTC data — do we count **3 runs**, or sum **quantities / pieces** on each row, or something else?*

- [x] **B1.1** **Each trip row = 1 run** (sum all rows that day for the same vehicle).  
- [ ] **B1.2** **Sum quantities from the data** (e.g. quantity / pieces column — if the company defines that as the P&L “run”). Specify if not the default column: _________________________  
- [ ] **B1.3** **Other** (1–2 sentences): _________________________  

**PM comments:** One trip row = one run; aggregate to a daily total per vehicle.  

---

### B2 — When a row from ATMTC is **not matched** to an employee or vehicle in Izumi Cloud

*(The system cannot confidently say “who / which vehicle” in the internal directory.)*

- [x] **B2.1** **Skip** those rows when updating P&L; report **partial** success so operations can fix master data later.  
- [ ] **B2.2** **Reject the whole update** if any row is unmatched — master/data must be fixed first, then rerun.  
- [ ] **B2.3** **Other:** _________________________  

**PM comments:** Unmatched rows are skipped and recorded as partial success. Notify the responsible owner by the next business day with counts and details of skipped rows.  

---

### B3 — Same **vehicle**, same **day**, but **several drivers** on different rows

- [x] **B3.1** **Accept:** still record **all** valid “who drove which vehicle that day” per row; the **total run count that day for that vehicle** is **one rolled-up number** (per B1).  
- [ ] **B3.2** **Other** (describe): _________________________  

**PM comments:** Multiple drivers on the same vehicle the same day are accepted. Keep per-row driver attribution; rolled-up run count for that vehicle-day follows B1.  

---

### B4 — After updating “who drove” and “run count” from ATMTC, should we also run **driver-based allocation** like the **timesheet** sync?

*(Affects P&L allocation numbers — align with PM / finance / operations.)*

- [ ] **B4.1** **Yes — full:** run **both** driver allocation **and** salary/run-count related steps **as in the current timesheet flow** on vehicle P&L.  
- [x] **B4.2** **Only** salary/run-count part; **not** the full driver allocation like timesheet.  
- [ ] **B4.3** **Need another meeting** (who / when): _________________________  

**PM comments:** ATMTC rows are delivery-trip records with a different source and grain than timesheets. Run only salary/run-count related steps, not the full timesheet-like driver allocation, to avoid double-counting; expand scope after finance review if needed.  

---

### B5 — Restrict **“sync daily run counts”** on vehicle P&L to **administrator** accounts only

*(Security: avoid normal accounts pushing operational numbers. May affect legacy integrations on lower privilege.)*

- [x] **B5.1** **Agree** — only admin accounts; other systems must switch to equivalent credentials.  
- [ ] **B5.2** **Need to keep** a separate path for system/partner: _________________________  
- [ ] **B5.3** **Not sure** — ask IT who still calls this function, then inform PM.  

**PM comments:** **B5.1 confirmed:** every caller must use **MASTER / high-privilege** VPL credentials for sync APIs in this scope (including `daily-operating/sync` and `atmtc-transactions/sync`). **B5.2** applies only if PM later records a **named exception** with a separate integration path.  

---

### B6 — On the vehicle P&L **daily summary** screen, show **run count** in this release?

- [ ] **B6.1** **Yes** — quick visibility on screen (minimal scope as proposed by engineering).  
- [x] **B6.2** **Not** in this release — **sync log** is enough for audit.  
- [ ] **B6.3** **Other:** _________________________  

**PM comments:** Run count on the daily summary screen is **not** required this release; audit via sync log is sufficient.  


## Appendix — Terms (only when talking to IT)

| What PM might say | What IT means (very short) |
|-------------------|----------------------------|
| **Vehicle P&L / VPL** | System that calculates by vehicle, by day/month. |
| **Izumi Cloud** | Company cloud app — stores trip table from ATMTC. |
| **Run count** | Number used to spread part of salary cost by trips per day. |
| **Admin (MASTER) role** | Account type for sensitive sync operations only. |
| **Matched employee/vehicle row** | Cloud resolved the row to the right person/vehicle in Izumi master data. |

*Technical detail for developers:* `docs/issues/Izumi_Issue-Requests-Repo/1044/plan.md`
