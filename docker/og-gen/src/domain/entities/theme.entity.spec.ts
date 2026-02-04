import { describe, expect, test } from "bun:test";
import { Theme } from "./theme.entity";

describe("Theme", () => {
  test("should create dark theme by default", () => {
    const theme = Theme.fromString(null);
    expect(theme.isDark()).toBe(true);
    expect(theme.value).toBe("dark");
  });

  test("should create light theme when specified", () => {
    const theme = Theme.fromString("light");
    expect(theme.isDark()).toBe(false);
    expect(theme.value).toBe("light");
  });

  test("should create dark theme for any value other than 'light'", () => {
    const theme = Theme.fromString("anything-else");
    expect(theme.isDark()).toBe(true);
    expect(theme.value).toBe("dark");
  });

  test("should return correct colors for dark theme", () => {
    const theme = Theme.fromString("dark");
    const colors = theme.getColors();

    expect(colors.bg).toBe("bg-[#141020]");
    expect(colors.card).toBe("bg-[#1e1830]");
    expect(colors.border).toBe("border-[#322d46]");
    expect(colors.text).toBe("text-[#e6e6f0]");
    expect(colors.mutedText).toBe("text-[#a0a0b4]");
    expect(colors.accent).toBe("bg-[#588cff]");
    expect(colors.pillBg).toBe("bg-[#292f59]");
    expect(colors.pillText).toBe("text-[#588cff]");
  });

  test("should return correct colors for light theme", () => {
    const theme = Theme.fromString("light");
    const colors = theme.getColors();

    expect(colors.bg).toBe("bg-[#f5f6fa]");
    expect(colors.card).toBe("bg-white");
    expect(colors.border).toBe("border-[#e6e6e6]");
    expect(colors.text).toBe("text-[#2c1e47]");
    expect(colors.mutedText).toBe("text-[#787878]");
    expect(colors.accent).toBe("bg-[#0b5ed7]");
    expect(colors.pillBg).toBe("bg-[#e7effb]");
    expect(colors.pillText).toBe("text-[#0b5ed7]");
  });
});