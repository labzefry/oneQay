// A timeout is an unknown outcome, never proof that a mutation was rejected.
// Callers must retain the operation ID and reconcile/retry that same operation.
export async function withOperationTimeout<T>(operation: Promise<T>, milliseconds = 25000): Promise<T> {
  let timer: ReturnType<typeof setTimeout> | undefined;
  try {
    return await Promise.race([
      operation,
      new Promise<never>((_, reject) => {
        timer = setTimeout(() => reject(new Error('SERVICE_OPERATION_TIMEOUT_RECONCILE_REQUIRED')), milliseconds);
      }),
    ]);
  } finally {
    clearTimeout(timer);
  }
}
