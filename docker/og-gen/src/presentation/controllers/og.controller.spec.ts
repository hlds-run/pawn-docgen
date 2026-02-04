import { describe, expect, test, beforeEach, vi, Mocked } from "bun:test";
import { OgController } from "./og.controller";
import { GetOgImageHandler } from "../../application/queries/get-og-image/get-og-image.handler";
import { GetOgPreviewHandler } from "../../application/queries/get-og-preview/get-og-preview.handler";
import { GetOgImageQuery } from "../../application/queries/get-og-image/get-og-image.query";

// Create mock objects for dependencies
const mockGetOgImageHandler = {
  execute: vi.fn(),
} as Mocked<GetOgImageHandler>;

const mockGetOgPreviewHandler = {
  execute: vi.fn(),
} as Mocked<GetOgPreviewHandler>;

describe("OgController", () => {
  let controller: OgController;

  beforeEach(() => {
    controller = new OgController(
      mockGetOgImageHandler,
      mockGetOgPreviewHandler
    );
    
    // Clear mocks before each test
    vi.clearAllMocks();
  });

  describe("render", () => {
    test("should return image response when valid parameters are provided", async () => {
      // Prepare data
      const mockRequest = new Request("http://localhost/og?title=Test&subtitle=Subtitle&tag=Tag");
      const mockImageBuffer = new Uint8Array([1, 2, 3, 4]);

      mockGetOgImageHandler.execute.mockResolvedValue(mockImageBuffer);

      // Execute
      const response = await controller.render(mockRequest);

      // Verify results
      expect(response.status).toBe(200);
      expect(response.headers.get("Content-Type")).toBe("image/png");
      expect(mockGetOgImageHandler.execute).toHaveBeenCalled();
    });

    test("should return 403 when signature is invalid", async () => {
      // Prepare data
      const mockRequest = new Request("http://localhost/og?title=Test&s=invalid");

      mockGetOgImageHandler.execute.mockRejectedValue(new Error("Invalid signature"));

      // Execute
      const response = await controller.render(mockRequest);

      // Verify results
      expect(response.status).toBe(403);
      expect(await response.text()).toBe("Invalid signature");
    });

    test("should return 500 when other error occurs", async () => {
      // Prepare data
      const mockRequest = new Request("http://localhost/og?title=Test");

      mockGetOgImageHandler.execute.mockRejectedValue(new Error("Unexpected error"));

      // Execute
      const response = await controller.render(mockRequest);

      // Verify results
      expect(response.status).toBe(500);
      expect(await response.text()).toBe("Unexpected error");
    });
  });

  describe("preview", () => {
    test("should return HTML preview when valid parameters are provided", async () => {
      // Prepare data
      const mockRequest = new Request("http://localhost/og/preview?title=Test&subtitle=Subtitle&tag=Tag");
      const mockHtmlString = "<html><body>Preview</body></html>";

      mockGetOgPreviewHandler.execute.mockResolvedValue(mockHtmlString);

      // Execute
      const response = await controller.preview(mockRequest);

      // Verify results
      expect(response.status).toBe(200);
      expect(response.headers.get("Content-Type")).toBe("text/html; charset=utf-8");
      expect(mockGetOgPreviewHandler.execute).toHaveBeenCalled();
    });
  });
});