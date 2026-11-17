import { ImageRenderer } from "../../../domain/interfaces/image-renderer.interface";
import { SecurityProvider } from "../../../domain/interfaces/security-provider.interface";
import { GetOgImageQuery } from "./get-og-image.query";

export class GetOgImageHandler {
  constructor(
    private readonly renderer: ImageRenderer<Uint8Array>,
    private readonly security: SecurityProvider,
  ) {}

  async execute(query: GetOgImageQuery): Promise<Uint8Array> {
    const { image, signature } = query;

    const isValid = this.security.verify(image.title, signature);
    if (!isValid) {
      throw new Error("Invalid signature");
    }

    return await this.renderer.render(image);
  }
}
