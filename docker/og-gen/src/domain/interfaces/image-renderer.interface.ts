import { OgImage } from "../entities/og-image.entity";

export interface ImageRenderer<T = Uint8Array> {
  render(image: OgImage): Promise<T>;
}
