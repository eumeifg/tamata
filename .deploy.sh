#!/bin/bash
set -e

export webTAG="web-${IMAGETAG}"
export fpmTAG="fpm-${IMAGETAG}"

yq e -i '(.magentoWeb.image.tag) = strenv(webTAG)' magento/values-${ENV}.yaml
yq e -i '(.magentoFpm.image.tag) = strenv(fpmTAG)' magento/values-${ENV}.yaml
helm secrets lint -f magento/values-${ENV}.yaml -f secrets://magento/secrets-${ENV}.enc.yaml magento/

checkout() {
    git add --all && \
    git commit -m "magento image tags updated to ${webTAG} and ${fpmTAG}" &&  \
    git push -u origin HEAD:master
}

if (checkout; [ $? -eq 0 ]) ; then
    echo "**SUCCESS** Magento app will be deployed soon"
    argocd login --username $ARGOCD_USERNAME --password $ARGOCD_PASSWORD argocd.creativeadvtech.ml
    argocd app get --grpc-web ${PROJECT}-${ENV}-magento
    # argocd app wait --grpc-web ${PROJECT}-${ENV}-magento
else
    echo "**WARNING** nothing has changed, exit"
fi
