import { describe, expect, test, beforeEach, vi } from "bun:test";
import { createElement } from "react";
import { TakumiRenderer } from "./takumi.renderer";
import { FontLoaderService } from "../fonts/font-loader.service";
import { OgImage } from "../../domain/entities/og-image.entity";
import { Theme } from "../../domain/entities/theme.entity";

// Mock ImageResponse
class MockImageResponse {
  constructor(public element: any, public options: any) {}

  async arrayBuffer() {
    return new Uint8Array([1, 2, 3, 4, 5]);
  }
}

vi.mock("@takumi-rs/image-response", () => ({
  ImageResponse: MockImageResponse,
}));

describe("TakumiRenderer", () => {
  let takumiRenderer: TakumiRenderer;
  let mockFontLoader: FontLoaderService;

  beforeEach(() => {
    mockFontLoader = {
      getFontData: vi.fn(),
    } as any;

    takumiRenderer = new TakumiRenderer(mockFontLoader);
  });

  test("should render image correctly with font data", async () => {
    const theme = Theme.fromString("dark");
    const image = new OgImage({
      title: "Test Title",
      subtitle: "Test Subtitle",
      tag: "Test Tag",
      theme: theme,
    });

    const fontRegularData = new ArrayBuffer(100);
    const fontSemiData = new ArrayBuffer(150);

    vi.spyOn(mockFontLoader, 'getFontData')
      .mockImplementation(async (fileName: string) => {
        if (fileName === "IBMPlexSans-Regular.ttf") {
          return fontRegularData;
        } else if (fileName === "IBMPlexSans-SemiBold.ttf") {
          return fontSemiData;
        }
        throw new Error("Unknown font");
      });

    const result = await takumiRenderer.render(image);

    expect(mockFontLoader.getFontData).toHaveBeenCalledWith("IBMPlexSans-Regular.ttf");
    expect(mockFontLoader.getFontData).toHaveBeenCalledWith("IBMPlexSans-SemiBold.ttf");
    expect(result).toBeInstanceOf(Uint8Array);
    expect(result.length).toBeGreaterThan(0);
  });

  test("should transform React elements to Takumi nodes correctly", () => {
    // Test the static toTakumiNodes method
    const reactElement = createElement('div', { className: 'test-class' }, 'Test Child');
    const transformedElement = TakumiRenderer.toTakumiNodes(reactElement);

    // Check that className was converted to tw
    expect((transformedElement as any).props.tw).toBe('test-class');
    expect((transformedElement as any).props.className).toBeUndefined();
    // Children might be an array, so check both cases
    const children = (transformedElement as any).props.children;
    if (Array.isArray(children)) {
      expect(children[0]).toBe('Test Child');
    } else {
      expect(children).toBe('Test Child');
    }
  });
});