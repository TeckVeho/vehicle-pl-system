import { afterEach, describe, expect, it } from "vitest";
import {
  DEFAULT_VISIBLE_LOCATION_CODES,
  filterVisibleLocations,
  getVisibleLocationCodes,
  visibleLocationPrismaWhere,
} from "./visible-locations.js";

const sampleLocations = [
  { id: "1", code: "LOC001", name: "本社" },
  { id: "2", code: "LOC002", name: "横浜第1" },
  { id: "3", code: "LOC017", name: "浜松" },
  { id: "4", code: "LOC004", name: "横浜第2" },
];

describe("getVisibleLocationCodes", () => {
  const original = process.env.VISIBLE_LOCATION_CODES;

  afterEach(() => {
    if (original === undefined) {
      delete process.env.VISIBLE_LOCATION_CODES;
    } else {
      process.env.VISIBLE_LOCATION_CODES = original;
    }
  });

  it("returns default allowlist when env is unset", () => {
    delete process.env.VISIBLE_LOCATION_CODES;
    expect(getVisibleLocationCodes()).toEqual([...DEFAULT_VISIBLE_LOCATION_CODES]);
  });

  it("returns default allowlist when env is empty", () => {
    process.env.VISIBLE_LOCATION_CODES = "";
    expect(getVisibleLocationCodes()).toEqual([...DEFAULT_VISIBLE_LOCATION_CODES]);
  });

  it("parses comma-separated env override", () => {
    process.env.VISIBLE_LOCATION_CODES = "LOC001, LOC004";
    expect(getVisibleLocationCodes()).toEqual(["LOC001", "LOC004"]);
  });
});

describe("filterVisibleLocations", () => {
  const original = process.env.VISIBLE_LOCATION_CODES;

  afterEach(() => {
    if (original === undefined) {
      delete process.env.VISIBLE_LOCATION_CODES;
    } else {
      process.env.VISIBLE_LOCATION_CODES = original;
    }
  });

  it("keeps only default visible location codes", () => {
    delete process.env.VISIBLE_LOCATION_CODES;
    expect(filterVisibleLocations(sampleLocations)).toEqual([
      { id: "2", code: "LOC002", name: "横浜第1" },
      { id: "3", code: "LOC017", name: "浜松" },
    ]);
  });

  it("respects env override", () => {
    process.env.VISIBLE_LOCATION_CODES = "LOC001";
    expect(filterVisibleLocations(sampleLocations)).toEqual([
      { id: "1", code: "LOC001", name: "本社" },
    ]);
  });
});

describe("visibleLocationPrismaWhere", () => {
  const original = process.env.VISIBLE_LOCATION_CODES;

  afterEach(() => {
    if (original === undefined) {
      delete process.env.VISIBLE_LOCATION_CODES;
    } else {
      process.env.VISIBLE_LOCATION_CODES = original;
    }
  });

  it("returns prisma where clause for visible codes", () => {
    delete process.env.VISIBLE_LOCATION_CODES;
    expect(visibleLocationPrismaWhere()).toEqual({
      code: { in: ["LOC002", "LOC017"] },
    });
  });
});
