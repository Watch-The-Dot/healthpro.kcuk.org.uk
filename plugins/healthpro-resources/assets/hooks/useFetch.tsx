import { useState } from "react";

export default function useFetch<TSuccess>(initialState: TSuccess) {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [data, setData] = useState<TSuccess>(initialState);

  const fetch = (func: () => Promise<TSuccess>) => {
    setLoading(true);
    setError(null);

    return func()
      .then((data) => setData(data))
      .catch((err) => setError(err))
      .finally(() => setLoading(false));
  };

  return {
    fetch,
    loading,
    error,
    data,
  };
}
