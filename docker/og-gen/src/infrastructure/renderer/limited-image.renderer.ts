import { OgImage } from "../../domain/entities/og-image.entity";
import { ImageRenderer } from "../../domain/interfaces/image-renderer.interface";
import { RenderQueue } from "./render-queue";

export class LimitedImageRenderer implements ImageRenderer<Uint8Array> {
  private readonly queue: RenderQueue;

  constructor(
    private readonly renderer: ImageRenderer<Uint8Array>,
    limit: number,
    maxWaiting: number,
  ) {
    this.queue = new RenderQueue(limit, maxWaiting);
  }

  render(image: OgImage): Promise<Uint8Array> {
    return this.queue.run(() => this.renderer.render(image));
  }
}
