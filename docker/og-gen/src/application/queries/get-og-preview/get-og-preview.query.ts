import { OgImage } from "../../../domain/entities/og-image.entity";

export class GetOgPreviewQuery {
  constructor(public readonly image: OgImage) {}
}
