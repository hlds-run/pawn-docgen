import { describe, expect, test, beforeEach, vi } from "bun:test";
import { HmacSecurityProvider } from "./hmac-security.provider";

describe("HmacSecurityProvider", () => {
  let securityProvider: HmacSecurityProvider;

  beforeEach(() => {
    securityProvider = new HmacSecurityProvider("test-secret", true, 8);
  });

  test("should generate correct hash", () => {
    const data = "test-data";
    const hash = securityProvider.generateHash(data);
    
    // Check that hash has correct length (8 characters)
    expect(hash.length).toBe(8);
    // Check that hash is generated consistently
    expect(securityProvider.generateHash(data)).toBe(hash);
  });

  test("should verify correct hash", () => {
    const data = "test-data";
    const hash = securityProvider.generateHash(data);
    
    const isValid = securityProvider.verify(data, hash);
    
    expect(isValid).toBe(true);
  });

  test("should reject incorrect hash", () => {
    const data = "test-data";
    const wrongHash = "wrong-hash";
    
    const isValid = securityProvider.verify(data, wrongHash);
    
    expect(isValid).toBe(false);
  });

  test("should reject empty hash", () => {
    const data = "test-data";
    
    const isValid = securityProvider.verify(data, "");
    
    expect(isValid).toBe(false);
  });

  test("should return true when security is disabled", () => {
    const disabledSecurityProvider = new HmacSecurityProvider("test-secret", false, 8);
    const isValid = disabledSecurityProvider.verify("any-data", "any-hash");
    
    expect(isValid).toBe(true);
  });

  test("should return false when hash length doesn't match", () => {
    const data = "test-data";
    const expectedHash = securityProvider.generateHash(data);
    // Create a hash with different length
    const wrongLengthHash = expectedHash + "extra";
    
    const isValid = securityProvider.verify(data, wrongLengthHash);
    
    expect(isValid).toBe(false);
  });
});