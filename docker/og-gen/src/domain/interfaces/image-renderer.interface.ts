import { OgImage } from "../entities/og-image.entity";

export interface ImageRenderer<T> {
  render(image: OgImage): Promise<T>;
}
