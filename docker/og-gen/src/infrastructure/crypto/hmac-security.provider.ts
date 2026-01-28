import { createHmac, timingSafeEqual } from "crypto";
import { SecurityProvider } from "../../domain/interfaces/security-provider.interface";

export class HmacSecurityProvider implements SecurityProvider {
  constructor(
    private readonly secret: string,
    private readonly isEnabled: boolean,
    private readonly symbolsToCompare: number,
  ) {}

  verify(data: string, hash: string): boolean {
    if (!this.isEnabled) {
      return true;
    }

    if (!hash) {
      return false;
    }

    const expectedHash = this.generateHash(data);
    if (hash.length !== expectedHash.length) {
      return false;
    }

    if (!timingSafeEqual(Buffer.from(hash), Buffer.from(expectedHash))) {
      return false;
    }

    return true;
  }

  generateHash(data: string): string {
    const hash = createHmac("sha256", this.secret)
      .update(data)
      .digest("hex")
      .substring(0, this.symbolsToCompare);

    return hash;
  }
}
