import pulumi
import pulumi_aws as aws

# Config
config = pulumi.Config()
key_name = config.require("keyName")

ami = aws.ec2.get_ami(
    most_recent=True,
    owners=["amazon"],
    filters=[
        {"name": "name", "values": ["amzn2-ami-hvm-*-x86_64-gp2"]},
        {"name": "architecture", "values": ["x86_64"]},
    ],
)

security_group = aws.ec2.SecurityGroup(
    "webhook-sg",
    description="Allow HTTP and custom port 8000",
    ingress=[
        {
            "protocol": "tcp",
            "from_port": 80,
            "to_port": 80,
            "cidr_blocks": ["0.0.0.0/0"],
        },
        {
            "protocol": "tcp",
            "from_port": 8000,
            "to_port": 8000,
            "cidr_blocks": ["0.0.0.0/0"],
        },
        {
            "protocol": "tcp",
            "from_port": 22,
            "to_port": 22,
            "cidr_blocks": ["0.0.0.0/0"],
        },
    ],
    egress=[
        {
            "protocol": "-1",
            "from_port": 0,
            "to_port": 0,
            "cidr_blocks": ["0.0.0.0/0"],
        }
    ],
)

# User data script to install Docker and run Laravel container
user_data = """
#!/bin/bash

# Run all system-level commands with sudo
sudo yum update -y
sudo yum install -y docker git

sudo curl -L "https://github.com/docker/compose/releases/download/1.29.2/docker-compose-$(uname -s)-$(uname -m)" \
    -o /usr/local/bin/docker-compose

sudo chmod +x /usr/local/bin/docker-compose

sudo ln -s /usr/local/bin/docker-compose /usr/bin/docker-compose


sudo systemctl start docker
sudo usermod -a -G docker ec2-user

# Switch to ec2-user's home and run app setup as ec2-user
cd /home/ec2-user
sudo -u ec2-user git clone https://github.com/hdmquan/webdev_final_assigment.git
cd /home/ec2-user/webdev_final_assigment/backend

# Run Docker Compose as ec2-user
sudo -u ec2-user docker-compose up -d
"""


instance = aws.ec2.Instance(
    "livekit-webhook-instance",
    instance_type="t3.micro",
    ami=ami.id,
    key_name=key_name,
    vpc_security_group_ids=[security_group.id],
    user_data=user_data,
    tags={"Name": "LaravelApp"},
)

# Export public IP and a mock URL
pulumi.export("public_ip", instance.public_ip)
pulumi.export("url", pulumi.Output.concat("http://", instance.public_dns))
