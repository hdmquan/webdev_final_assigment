import pulumi
import pulumi_aws as aws

# Config
ami = aws.ec2.get_ami(
    most_recent=True,
    owners=["amazon"],
    filters=[{"name": "name", "values": ["amzn2-ami-hvm-*-x86_64-gp2"]}],
)

# Security Group
group = aws.ec2.SecurityGroup(
    "web-secgrp",
    description="Enable HTTP access",
    ingress=[
        {
            "protocol": "tcp",
            "from_port": 80,
            "to_port": 80,
            "cidr_blocks": ["0.0.0.0/0"],
        },
        {
            "protocol": "tcp",
            "from_port": 22,
            "to_port": 22,
            "cidr_blocks": ["0.0.0.0/0"],
        },
        {
            "protocol": "tcp",
            "from_port": 8000,
            "to_port": 8000,
            "cidr_blocks": ["0.0.0.0/0"],
        },
    ],
)

# SSH key name (must already exist in your AWS account)
key_name = "your-ssh-key-name"

# User data script to install Docker and run Laravel container
user_data = """

#!/bin/bash

yum update -y
yum install -y docker git
service docker start
usermod -a -G docker ec2-user

cd /home/ec2-user
git clone https://github.com/hdmquan/webdev_final_assigment.git
cd webdev_final_assigment/backend

docker-compose up -d

"""

# EC2 Instance
server = aws.ec2.Instance(
    "laravel-server",
    instance_type="t2.micro",
    vpc_security_group_ids=[group.id],
    ami=ami.id,
    key_name=key_name,
    user_data=user_data,
    tags={"Name": "LaravelApp"},
)

# Export public IP and a mock URL
pulumi.export("public_ip", server.public_ip)
pulumi.export("url", pulumi.Output.concat("http://", server.public_dns))
