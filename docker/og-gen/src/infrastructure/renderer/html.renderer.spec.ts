import { describe, expect, test, beforeEach, vi } from "bun:test";
import { HtmlRenderer } from "./html.renderer";
import { OgImage } from "../../domain/entities/og-image.entity";
import { Theme } from "../../domain/entities/theme.entity";

describe("HtmlRenderer", () => {
  let htmlRenderer: HtmlRenderer;

  beforeEach(() => {
    htmlRenderer = new HtmlRenderer();
  });

  test("should render HTML with proper structure", async () => {
    const theme = Theme.fromString("dark");
    const image = new OgImage({
      title: "Test Title",
      subtitle: "Test Subtitle",
      tag: "Test Tag",
      theme: theme,
    });

    const result = await htmlRenderer.render(image);

    // Check that result contains DOCTYPE html
    expect(result.startsWith("<!DOCTYPE html>")).toBe(true);
    // Check that result contains template elements
    expect(result.includes("Test Title")).toBe(true);
    expect(result.includes("Test Subtitle")).toBe(true);
    expect(result.includes("Test Tag")).toBe(true);
  });

  test("should handle image with minimal properties", async () => {
    const theme = Theme.fromString("light");
    const image = new OgImage({
      title: "Minimal Title",
      theme: theme,
    });

    const result = await htmlRenderer.render(image);

    expect(result.startsWith("<!DOCTYPE html>")).toBe(true);
    expect(result.includes("Minimal Title")).toBe(true);
  });
});