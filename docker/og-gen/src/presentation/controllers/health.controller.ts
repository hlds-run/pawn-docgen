export class HealthController {
  constructor(private readonly config: any) {}

  async health(req: Request): Promise<Response> {
    return new Response(
      JSON.stringify({
        status: "ok",
        hmac_enabled: this.config.checkHmac,
      }),
      { headers: { "Content-Type": "application/json" } },
    );
  }
}
