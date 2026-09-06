<?php

declare(strict_types=1);

namespace App\Application\Pos;

// Author by Lab | zefry
interface FinalShiftCloseRuntimeBindingMaterializer
{
    /**
     * @return array{
     *   state:string,
     *   operation_id:string,
     *   manifest_sha256:string,
     *   secrets_embedded:false
     * }
     */
    public function materialize(FinalShiftCloseRuntimeBindingMaterializationRequest $request): array;
}
