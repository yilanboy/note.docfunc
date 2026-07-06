# 從憑證取得 Serial Number

你可以使用 Python 從公有憑證中取得 Serial Number。

簡單的範例程式如下：

```python
import re
from cryptography import x509


def is_pem_format(cert: str):
    return re.search(
        r"^(-----BEGIN CERTIFICATE-----)[\S\s]*(-----END CERTIFICATE-----)$", cert
    )


def get_serial_number_from_certificate(cert: str):
    """
    calculate the certificate serial number
    ref: https://github.com/Azure/azure-sdk-for-python/issues/16965
    """
    if is_pem_format(cert) is False:
        raise Exception("Sorry, certificate is not correct pem format.")

    cert = x509.load_pem_x509_certificates(bytes(cert, "utf-8"))
    serial = cert[0].serial_number
    serial_number = f"{serial:x}".lower()

    return serial_number


with open("cert.pem", "r") as cert:
    serial_number = get_serial_number_from_certificate(cert.read())
    print(serial_number)
```

> 為什麼特地寫這一段，因為 Azure Key Vault 的 Certificate API 居然沒有回傳 Serial Number！
