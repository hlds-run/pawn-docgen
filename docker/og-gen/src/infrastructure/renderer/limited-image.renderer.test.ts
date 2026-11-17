import { expect, test } from "bun:test";
import { ImageRendererBusyError } from "../../application/errors/image-renderer-busy.error";
import { OgImage } from "../../domain/entities/og-image.entity";
import { Theme } from "../../domain/entities/theme.entity";
import { LimitedImageRenderer } from "./limited-image.renderer";

test("the renderer decorator limits concurrent work and forwards results", async () => {
  let release!: () => void;
  let calls = 0;
  const delegate = {
    render: async () => {
      calls++;
      await new Promise<void>((resolve) => { release = resolve; });
      return new Uint8Array([42]);
    },
  };
  const renderer = new LimitedImageRenderer(delegate, 1, 0);
  const image = new OgImage({ title: "Test", theme: Theme.fromString("dark") });
  const first = renderer.render(image);
  await expect(renderer.render(image)).rejects.toBeInstanceOf(ImageRendererBusyError);
  expect(calls).toBe(1);
  release();
  expect([...await first]).toEqual([42]);
});

test("allows eight waiting renders, rejects overflow, and continues after failure", async () => {
  let rejectFirst!: (error: Error) => void;
  let calls = 0;
  const delegate = {
    render: async () => {
      calls++;
      if (calls === 1) {
        return await new Promise<Uint8Array>((_, reject) => { rejectFirst = reject; });
      }
      return new Uint8Array([calls]);
    },
  };
  const renderer = new LimitedImageRenderer(delegate, 1, 8);
  const image = new OgImage({ title: "Test", theme: Theme.fromString("dark") });
  const first = renderer.render(image);
  const waiting = Array.from({ length: 8 }, () => renderer.render(image));
  expect(calls).toBe(1);
  await expect(renderer.render(image)).rejects.toBeInstanceOf(ImageRendererBusyError);
  rejectFirst(new Error("render failed"));
  await expect(first).rejects.toThrow("render failed");
  const results = await Promise.all(waiting);
  expect(results.map((result) => result[0])).toEqual([2, 3, 4, 5, 6, 7, 8, 9]);
  expect([...(await renderer.render(image))]).toEqual([10]);
});
