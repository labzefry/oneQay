import {test} from 'node:test';
import assert from 'node:assert/strict';
import {withOperationTimeout} from './operation-timeout.ts';
test('completed operations return their result', async () => {
  assert.deepEqual(await withOperationTimeout(Promise.resolve({id:'recorded'}), 20), {id:'recorded'});
});
test('server rejection remains unchanged', async () => {
  const error = new Error('OWN_SHIFT_REQUIRED');
  await assert.rejects(withOperationTimeout(Promise.reject(error), 20), e => e === error);
});
test('a stalled call releases the UI without claiming rejection', async () => {
  await assert.rejects(withOperationTimeout(new Promise(() => {}), 5), /TIMEOUT_RECONCILE_REQUIRED/);
});
test('late completion cannot turn the timed out result into success', async () => {
  let complete;
  const original = new Promise(resolve => {complete = resolve;});
  const result = withOperationTimeout(original, 5);
  await assert.rejects(result, /TIMEOUT_RECONCILE_REQUIRED/);
  complete({recorded:true});
  await assert.rejects(result, /TIMEOUT_RECONCILE_REQUIRED/);
});
