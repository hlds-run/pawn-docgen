import { describe, expect, test, beforeEach, vi, Mocked } from "bun:test";
import { GetOgPreviewHandler } from "./get-og-preview.handler";
import { ImageRenderer } from "../../../domain/interfaces/image-renderer.interface";
import { GetOgPreviewQuery } from "./get-og-preview.query";

// Create mock object for dependency
const mockRenderer = {
  render: vi.fn(),
} as Mocked<ImageRenderer<string>>;

describe("GetOgPreviewHandler", () => {
  let handler: GetOgPreviewHandler;
  let query: GetOgPreviewQuery;

  beforeEach(() => {
    handler = new GetOgPreviewHandler(mockRenderer);
    
    // Prepare test data
    query = {
      image: {
        title: "Test Preview Title",
        width: 1200,
        height: 630,
        // Add other required fields depending on interface definition
      },
    };

    // Clear mocks before each test
    vi.clearAllMocks();
  });

  test("should successfully render preview HTML", async () => {
    // Setup mocks
    const expectedHtml = "<html><body>Test Preview</body></html>";
    mockRenderer.render.mockResolvedValue(expectedHtml);

    // Execute
    const result = await handler.execute(query);

    // Verify results
    expect(mockRenderer.render).toHaveBeenCalledWith(query.image);
    expect(result).toBe(expectedHtml);
  });
});