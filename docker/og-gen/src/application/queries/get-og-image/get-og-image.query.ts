import { OgImage } from "../../../domain/entities/og-image.entity";

export class GetOgImageQuery {
  constructor(
    public readonly image: OgImage,
    public readonly signature: string,
  ) {}
}
