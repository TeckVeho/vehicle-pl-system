import { describe, expect, it } from "vitest";
import { extractFolderId } from "./google-drive-id";

describe("extractFolderId", () => {
  it("extracts id from /folders/ URL", () => {
    expect(extractFolderId("https://drive.google.com/drive/folders/abc123_xyz")).toBe(
      "abc123_xyz"
    );
  });

  it("extracts id from ?id= query param", () => {
    expect(extractFolderId("https://drive.google.com/open?id=sheetId99")).toBe(
      "sheetId99"
    );
  });

  it("returns trimmed raw id when no pattern matches", () => {
    expect(extractFolderId("  raw-folder-id  ")).toBe("raw-folder-id");
  });

  it("returns empty string for empty input", () => {
    expect(extractFolderId("   ")).toBe("");
  });

  it("prefers /folders/ over query id when both present", () => {
    expect(
      extractFolderId("https://drive.google.com/drive/folders/folderA?id=folderB")
    ).toBe("folderA");
  });
});
