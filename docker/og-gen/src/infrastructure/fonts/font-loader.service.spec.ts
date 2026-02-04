import { describe, expect, test, beforeEach, vi } from "bun:test";
import { FontLoaderService } from "./font-loader.service";

describe("FontLoaderService", () => {
  let fontLoader: FontLoaderService;

  beforeEach(() => {
    fontLoader = new FontLoaderService();
  });

  test("should load font data and cache it", async () => {
    // Mock Bun.file to return test data
    const testData = new Uint8Array([1, 2, 3, 4, 5]);
    const mockFile = {
      arrayBuffer: vi.fn().mockResolvedValue(testData.buffer),
      exists: vi.fn().mockResolvedValue(true),
    };

    // Mock Bun.file
    vi.spyOn(Bun, 'file').mockReturnValue(mockFile as any);

    const fileName = "test-font.ttf";
    const fontData1 = await fontLoader.getFontData(fileName);
    const fontData2 = await fontLoader.getFontData(fileName);

    // Check that file was read only once (due to caching)
    expect(mockFile.arrayBuffer).toHaveBeenCalledTimes(1);
    // Check that data matches
    expect(fontData1).toEqual(testData.buffer);
    expect(fontData1).toBe(fontData2); // Should be identical due to caching
  });

  test("should throw error if font file does not exist", async () => {
    const mockFile = {
      exists: vi.fn().mockResolvedValue(false),
    };
    
    vi.spyOn(Bun, 'file').mockReturnValue(mockFile as any);

    const fileName = "nonexistent-font.ttf";
    
    await expect(fontLoader.getFontData(fileName)).rejects.toThrow(
      `Font file not found: ./${fileName}. Make sure it is in the root directory.`
    );
  });
});