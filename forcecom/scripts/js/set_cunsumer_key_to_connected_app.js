const fs = require("fs");
const originDir = "./force-app/main/default/connectedApps/";
fs.readdirSync(originDir).forEach((filename) => {
  const cunsumerKey = require("crypto").randomBytes(64).toString("hex");
  let text = fs.readFileSync(originDir + filename).toString();
  text = text.replace(
    /<consumerKey>[0-9a-zA-Z_\-\.]*<\/consumerKey>/,
    `<consumerKey>${cunsumerKey}</consumerKey>`
  );
  fs.writeFileSync(`./force-app/main/default/connectedApps/${filename}`, text);
});
