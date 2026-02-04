import { describe, expect, test, beforeEach, vi, Mocked } from "bun:test";
import { GetOgImageHandler } from "./get-og-image.handler";
import { ImageRenderer } from "../../../domain/interfaces/image-renderer.interface";
import { SecurityProvider } from "../../../domain/interfaces/security-provider.interface";
import { GetOgImageQuery } from "./get-og-image.query";

// Create mock objects for dependencies
const mockRenderer = {
  render: vi.fn(),
} as Mocked<ImageRenderer<Uint8Array>>;

const mockSecurity = {
  verify: vi.fn(),
} as Mocked<SecurityProvider>;

describe("GetOgImageHandler", () => {
  let handler: GetOgImageHandler;
  let query: GetOgImageQuery;

  beforeEach(() => {
    handler = new GetOgImageHandler(mockRenderer, mockSecurity);
    
    // Prepare test data
    query = {
      image: {
        title: "Test Title",
        width: 1200,
        height: 630,
        // Add other required fields depending on interface definition
      },
      signature: "test-signature",
    };

    // Clear mocks before each test
    vi.clearAllMocks();
  });

  test("should successfully render image when signature is valid", async () => {
    // Setup mocks
    mockSecurity.verify.mockReturnValue(true);
    const expectedResult = new Uint8Array([1, 2, 3]);
    mockRenderer.render.mockResolvedValue(expectedResult);

    // Execute
    const result = await handler.execute(query);

    // Verify results
    expect(mockSecurity.verify).toHaveBeenCalledWith("Test Title", "test-signature");
    expect(mockRenderer.render).toHaveBeenCalledWith(query.image);
    expect(result).toEqual(expectedResult);
  });

  test("should throw error when signature is invalid", async () => {
    // Setup mocks
    mockSecurity.verify.mockReturnValue(false);

    // Verify exception
    await expect(handler.execute(query)).rejects.toThrow("Invalid signature");

    // Verify that renderer was not called
    expect(mockRenderer.render).not.toHaveBeenCalled();
  });
});