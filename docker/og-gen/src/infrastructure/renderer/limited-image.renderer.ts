import PQueue from "p-queue";
import { ImageRendererBusyError } from "../../application/errors/image-renderer-busy.error";
import { OgImage } from "../../domain/entities/og-image.entity";
import { ImageRenderer } from "../../domain/interfaces/image-renderer.interface";

export class LimitedImageRenderer implements ImageRenderer<Uint8Array> {
  private readonly queue: PQueue;

  constructor(
    private readonly renderer: ImageRenderer<Uint8Array>,
    limit: number,
    private readonly maxWaiting: number,
  ) {
    this.queue = new PQueue({ concurrency: limit });
  }

  render(image: OgImage): Promise<Uint8Array> {
    if (this.queue.pending >= this.queue.concurrency && this.queue.size >= this.maxWaiting) {
      return Promise.reject(new ImageRendererBusyError());
    }
    return this.queue.add(() => this.renderer.render(image));
  }
}
