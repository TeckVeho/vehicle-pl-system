import nextCoreWebVitals from "eslint-config-next/core-web-vitals";

const eslintConfig = [
  {
    ignores: ["backend/**", "db-dumps/**", "google-cloud/**"],
  },
  ...nextCoreWebVitals,
  {
    rules: {
      // react-hooks v7 (via eslint-config-next 16) flags the existing
      // fetch-on-mount pattern. Keep the previous lint policy for this bump.
      "react-hooks/set-state-in-effect": "off",
    },
  },
];

export default eslintConfig;
