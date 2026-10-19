/**
 * Express 5 types `req.params` values as `string | string[]` because a route
 * can repeat a named wildcard. These handlers only use a single `:param`.
 */
export function routeParam(value: string | string[] | undefined): string {
  if (Array.isArray(value)) return value[0] ?? "";
  return value ?? "";
}
