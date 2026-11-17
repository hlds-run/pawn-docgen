export class ImageRendererBusyError extends Error {
  constructor() {
    super("Image renderer is at capacity");
    this.name = "ImageRendererBusyError";
  }
}
