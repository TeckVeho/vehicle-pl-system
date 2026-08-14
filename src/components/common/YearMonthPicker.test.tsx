import { render, screen } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";
import { YearMonthPicker } from "./YearMonthPicker";

describe("YearMonthPicker", () => {
  it("renders controlled year and month values", () => {
    render(
      <YearMonthPicker
        year={2026}
        month={3}
        onYearChange={vi.fn()}
        onMonthChange={vi.fn()}
      />
    );

    expect(screen.getByText("2026年")).toBeInTheDocument();
    expect(screen.getByText("3月")).toBeInTheDocument();
    expect(screen.getAllByRole("combobox")).toHaveLength(2);
  });
});
