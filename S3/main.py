import boto3
session = boto3.session.Session()
s3_client = session.client(
    service_name = 's3',
    endpoint_url = 'https://hb.bizmrg.com',
    aws_access_key_id = 'ioJHUeiHLHF9b8sW2eLBsc',
    aws_secret_access_key = '5sSENeUviPoFp7c6nZ6c5GTJzguLzcezofd1tJig3dKc'
)
test_bucket_name = 'boto3-test-bucket-name'
# Создаем бакет
s3_client.create_bucket(Bucket=test_bucket_name)
response = s3_client.list_buckets()
print(response)


for key in response['Buckets']:
    print(key['Name'])

    test_bucket_name = 'boto3-test-bucket-name'

    # Загрузка данных из строки
    s3_client.put_object(Body='TEST_TEXT_TEST_TEXT', Bucket=test_bucket_name, Key='test_file.txt')

    # Загрузка локального файла
    s3_client.upload_file('some_test_file_from_local.txt', test_bucket_name, 'copy_some_test_file.txt')

    # Загрузка локального файла в директорию внутри бакета
    s3_client.upload_file('some_test_file_from_local.txt', test_bucket_name, 'backup_dir/copy_some_test_file.txt')


response = s3_client.get_object(Bucket='boto3-test-bucket-name', Key='test_file.txt')
print(response)
print(response['Body'].read())


test_bucket_name = 'boto3-test-bucket-name'


for key in s3_client.list_objects(Bucket=test_bucket_name)['Contents']:
    print(key['Key'])