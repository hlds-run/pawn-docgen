import { Theme } from "./theme.entity";

export interface OgImageProps {
  title: string;
  subtitle?: string;
  tag?: string;
  theme: Theme;
}

export class OgImage {
  public readonly title: string;
  public readonly subtitle: string;
  public readonly tag: string;
  public readonly theme: Theme;

  public readonly titleMaxLength: number = 86;
  public readonly subtitleMaxLength: number = 450;

  constructor(props: OgImageProps) {
    this.title = this.validateTitle(props.title);
    this.subtitle = this.validateSubtitle(props.subtitle);
    this.tag = props.tag || "Pawn";
    this.theme = props.theme;
  }

  private validateTitle(title: string): string {
    const trimmed = title.trim();
    if (!trimmed) {
      throw new Error(`Can't create OgImage with empty title`);
    }

    if (trimmed.length > this.titleMaxLength) {
      return trimmed.substring(0, this.titleMaxLength - 1) + "…";
    }

    return trimmed;
  }

  private validateSubtitle(title: string = ""): string {
    const trimmed = title.trim();
    if (trimmed.length > this.subtitleMaxLength) {
      return trimmed.substring(0, this.subtitleMaxLength - 1) + "…";
    }

    return trimmed;
  }

  getDimensions() {
    return { width: 1200, height: 630 };
  }
}
