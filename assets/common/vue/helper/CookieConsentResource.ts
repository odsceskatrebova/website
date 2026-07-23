interface CookieConsentResource {
  send(preferences: string[]): Promise<void>;
}

export function useCookieConsentResource(): CookieConsentResource {
  const send = async (preferences: string[]): Promise<void> => {
    await fetch('/api/cookie_consent', {
      method: 'POST',
      headers: { 'Content-Type': 'application/ld+json' },
      body: JSON.stringify({ preferences }),
    });
  };

  return { send };
}
