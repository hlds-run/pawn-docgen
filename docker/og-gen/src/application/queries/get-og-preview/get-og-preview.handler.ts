import { ImageRenderer } from "../../../domain/interfaces/image-renderer.interface";
import { GetOgPreviewQuery } from "./get-og-preview.query";

export class GetOgPreviewHandler {
  constructor(private readonly renderer: ImageRenderer<string>) {}

  async execute(query: GetOgPreviewQuery): Promise<string> {
    const { image } = query;

    return await this.renderer.render(image);
  }
}
