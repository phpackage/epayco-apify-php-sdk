<?php

namespace Phpackage\Epayco\Apify\Api;

final class BillCollect extends AbstractApi
{
    public function createInvoice(array $data)
    {
        $response = $this
            ->client
            ->post('/billcollect/invoices/create', $data);

        return $response;
    }
}
