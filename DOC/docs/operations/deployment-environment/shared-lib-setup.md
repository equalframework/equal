## Shared-lib

When developing custom application in a package of eQual, things can be tough. 

For example, it can be hard to know where is the eQual root URL or you may want to use the built-in view renderer of eQual UI.

To make things easier, you can use `sb-shared-lib` an Angular lib that allow you to access eQual the same way as a native eQual app.


### Compiling `shared-lib`

### Node version

To compile  `sb-shared-lib` you will need to use node 14.18.

If you need to install it aside of another node installation, you may use [Node Version Manager](https://github.com/nvm-sh/nvm) as follow :
```bash
nvm install 14.18
```
this allow your current directory to be bound to this specifically node version.

### Build SharedLib

Next, you'll need to clone symbioseUI and build it.

```
git clone https://github.com/yesbabylon/symbiose-ui.git
# you probably need to checkout to dev-2.0 to get newest version of symbioseUI
cd symbiose-ui
git checkout dev-2.0
git pull
npm install
cd sb-shared-lib
npm install
ng build
cd dist/sb-shared-lib
npm link 
```

Once you did this you only need to add equal.bundle.js to the library folder you linked

### eQUI (eQual UI)

Finally, you can clone and build eQualUI

```
git clone https://github.com/equalframework/equal-ui.git
cd equal-ui
npm install
sh export.sh
```

---