export type ThemeType = "dark" | "light";

export interface ColorsProps {
  bg: string;
  card: string;
  border: string;
  text: string;
  mutedText: string;
  accent: string;
  pillBg: string;
  pillText: string;
}

export class Theme {
  private constructor(public readonly value: ThemeType) {}

  static fromString(theme: string | null): Theme {
    return theme === "light" ? new Theme("light") : new Theme("dark");
  }

  isDark(): boolean {
    return this.value === "dark";
  }

  getColors(): ColorsProps {
    if (this.isDark()) {
      return {
        bg: "bg-[#141020]",
        card: "bg-[#1e1830]",
        border: "border-[#322d46]",
        text: "text-[#e6e6f0]",
        mutedText: "text-[#a0a0b4]",
        accent: "bg-[#588cff]",
        pillBg: "bg-[#292f59]",
        pillText: "text-[#588cff]",
      };
    }

    return {
      bg: "bg-[#f5f6fa]",
      card: "bg-white",
      border: "border-[#e6e6e6]",
      text: "text-[#2c1e47]",
      mutedText: "text-[#787878]",
      accent: "bg-[#0b5ed7]",
      pillBg: "bg-[#e7effb]",
      pillText: "text-[#0b5ed7]",
    };
  }
}
