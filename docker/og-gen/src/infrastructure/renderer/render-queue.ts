import { ImageRendererBusyError } from "../../application/errors/image-renderer-busy.error";

// Limit concurrent native image renders and bound memory held by waiting requests.
export class RenderQueue {
  private active = 0;
  private readonly waiting: Array<() => void> = [];

  constructor(private readonly limit: number, private readonly maxWaiting: number) {}

  async run<T>(render: () => Promise<T>): Promise<T> {
    if (this.active >= this.limit) {
      if (this.waiting.length >= this.maxWaiting) {
        throw new ImageRendererBusyError();
      }
      await new Promise<void>((resolve) => this.waiting.push(resolve));
    } else {
      this.active++;
    }

    try {
      return await render();
    } finally {
      const next = this.waiting.shift();
      if (next) {
        next();
      } else {
        this.active--;
      }
    }
  }
}
