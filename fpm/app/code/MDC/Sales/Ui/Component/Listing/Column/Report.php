<?php
namespace MDC\Sales\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class Report extends Column
{
    /**
     * Prepare Data Source
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (!array_key_exists('report_url', $item) || $item['report_url'] == '') {
                    continue;
                }

                $reportHtml = __('<a href="%1">Download</a>', $item['report_url']);
                $item['report_url'] = $reportHtml;
            }
        }

        return $dataSource;
    }
}
