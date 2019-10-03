# snce
Snce Symfony 4 task

How to deploy this app:

1. `git clone https://github.com/Zariwey0/snce_task.git`
2. `cd snce_task`
3. `sudo docker-compose up -d`
4. take a nap

Now we should join the php bash:

5. `sudo docker exec -it -u dev sf4_php bash`
6. `cd sf4` 
7. `sudo composer install`
8. `sudo chmod 777 public/uploads/images/` (in order to be able to upload images)
9. join localhost/product
