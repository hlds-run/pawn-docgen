import { describe, expect, test } from "bun:test";
import { OgImage } from "./og-image.entity";
import { Theme } from "./theme.entity";

describe("OgImage", () => {
  test("should create OgImage with valid properties", () => {
    const theme = Theme.fromString("dark");
    const props = {
      title: "Test Title",
      subtitle: "Test Subtitle",
      tag: "Test Tag",
      theme: theme,
    };

    const ogImage = new OgImage(props);

    expect(ogImage.title).toBe("Test Title");
    expect(ogImage.subtitle).toBe("Test Subtitle");
    expect(ogImage.tag).toBe("Test Tag");
    expect(ogImage.theme).toBe(theme);
  });

  test("should set default tag if not provided", () => {
    const theme = Theme.fromString("dark");
    const props = {
      title: "Test Title",
      subtitle: "Test Subtitle",
      theme: theme,
    };

    const ogImage = new OgImage(props);

    expect(ogImage.tag).toBe("Pawn"); // Default value
  });

  test("should trim title and subtitle", () => {
    const theme = Theme.fromString("dark");
    const props = {
      title: "  Test Title  ",
      subtitle: "  Test Subtitle  ",
      theme: theme,
    };

    const ogImage = new OgImage(props);

    expect(ogImage.title).toBe("Test Title");
    expect(ogImage.subtitle).toBe("Test Subtitle");
  });

  test("should throw error if title is empty after trimming", () => {
    const theme = Theme.fromString("dark");
    const props = {
      title: "   ",
      theme: theme,
    };

    expect(() => new OgImage(props)).toThrow("Can't create OgImage with empty title");
  });

  test("should truncate title if it exceeds max length", () => {
    const theme = Theme.fromString("dark");
    const longTitle = "a".repeat(100); // Exceeds maximum length of 86
    const props = {
      title: longTitle,
      theme: theme,
    };

    const ogImage = new OgImage(props);

    expect(ogImage.title.length).toBe(ogImage.titleMaxLength);
    expect(ogImage.title.endsWith("…")).toBe(true);
  });

  test("should truncate subtitle if it exceeds max length", () => {
    const theme = Theme.fromString("dark");
    const longSubtitle = "a".repeat(500); // Exceeds maximum length of 450
    const props = {
      title: "Test Title",
      subtitle: longSubtitle,
      theme: theme,
    };

    const ogImage = new OgImage(props);

    expect(ogImage.subtitle.length).toBe(ogImage.subtitleMaxLength);
    expect(ogImage.subtitle.endsWith("…")).toBe(true);
  });

  test("should return correct dimensions", () => {
    const theme = Theme.fromString("dark");
    const props = {
      title: "Test Title",
      theme: theme,
    };

    const ogImage = new OgImage(props);
    const dimensions = ogImage.getDimensions();

    expect(dimensions.width).toBe(1200);
    expect(dimensions.height).toBe(630);
  });
});