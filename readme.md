# Page and Port Monitor
Monitor if a webpage still loads (and optionally contains certain text) or if a TCP port is accepting connections.  
Sends an email when a fault is detected.

Initial login: admin@pnpmonitor.com / admin

# Docker run
    docker run -d -p 80:80 jeftadirksen/pnpmonitor

# Docker build
    git clone https://github.com/JeftaDirksen/PnPMonitor.git
    cd PnPMonitor
    docker build -t pnpmonitor .
    docker run -d -p 80:80 pnpmonitor
