import { expect, test } from "bun:test";
import { RenderQueue, RenderQueueFullError } from "./render-queue";

test("limits concurrent renders, bounds the queue, and recovers after errors", async () => {
  const queue = new RenderQueue(1, 1);
  let release!: () => void;
  const started: string[] = [];
  const first = queue.run(async () => {
    started.push("first");
    await new Promise<void>((resolve) => { release = resolve; });
    throw new Error("render failed");
  });
  const second = queue.run(async () => { started.push("second"); return 2; });
  expect(started).toEqual(["first"]);
  await expect(queue.run(async () => 3)).rejects.toBeInstanceOf(RenderQueueFullError);
  release();
  await expect(first).rejects.toThrow("render failed");
  expect(await second).toBe(2);
  expect(started).toEqual(["first", "second"]);
  expect(await queue.run(async () => 4)).toBe(4);
});
