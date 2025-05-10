DIR=$(dirname $(readlink -f $0))
rm -rf $DIR/app
git clone git@bitbucket.org:motvicka/eurotours.git $DIR/app
cp $DIR/app/docker/* $DIR/
cp $DIR/parameters.yml $DIR/app/app/config/parameters.yml
cd $DIR && docker compose up --build -d
