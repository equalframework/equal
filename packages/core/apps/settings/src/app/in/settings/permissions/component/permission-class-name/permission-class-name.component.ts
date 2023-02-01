import { Component, OnInit, Output , Input,EventEmitter, SimpleChange, SimpleChanges} from '@angular/core';
import { ApiService } from 'sb-shared-lib';
import { FormControl } from '@angular/forms';
import { debounceTime, pairwise, startWith } from 'rxjs/operators';

@Component({
  selector: 'app-permission-class-name',
  templateUrl: './permission-class-name.component.html',
  styleUrls: ['./permission-class-name.component.scss']
})
export class PermissionClassNameComponent implements OnInit {

  constructor(private api: ApiService) { }


  public level1:FormControl = new FormControl();
  public level2:FormControl = new FormControl();
  public level3:FormControl = new FormControl();
  public level4:FormControl = new FormControl();
  


  @Input() className:string = '*';
  @Output() change:EventEmitter<string> = new EventEmitter<string>();

  public allLevel1: any;
  public allLevel2: any;
  public allLevel3: any;
  public allLevel4: any;


  async ngOnChanges(changes:SimpleChanges) {
    const currentClassName:SimpleChange = changes.className;

    if(currentClassName && currentClassName.currentValue && currentClassName.currentValue != currentClassName.previousValue) {
      this.className = currentClassName.currentValue;
      if(this.className.includes('\\')){
        let classNameTable = this.className.split("\\");
        this.level1.setValue(classNameTable[0]);
        if(this.level1.value)this.level2.setValue(classNameTable[1]);
        if(this.level2.value)this.level3.setValue(classNameTable[2]); 
        if(this.level3.value)this.level4.setValue(classNameTable[3]);
        this.getAllLevels();
      }else{
        this.level1.setValue(this.className);
        this.getAllLevels();
      }
    }else{
      this.level1.setValue('*');
      this.getAllLevels();
    }
  }


  async ngOnInit(){ 
  
  }

  public onChangeLevel1(event: any) {
    let fullClassName:string = <string> event.value;
    this.level2.setValue('*');
    this.level3.reset();
    this.level4.reset();
    this.change.emit(fullClassName);
    this.getAllLevels();
  }

  public onChangeLevel2(event: any) {
    let fullClassName:string = this.level1.value + '\\'+ <string> event.value;
    if(this.level2.value == this.level2.value.toLowerCase()) {
      this.level3.setValue('*');
    }
    else {
      this.level3.reset();
    }    
    this.level4.reset();
    this.change.emit(fullClassName);
    this.getAllLevels();
  }
  
  public onChangeLevel3(event: any) {
    let fullClassName = this.level1.value + '\\' + this.level2.value + '\\' + <string> event.value;
    if(this.level3.value == this.level3.value.toLowerCase()) {
      this.level4.setValue('*');
    }
    else {
      this.level4.reset();
    }
    this.change.emit(fullClassName);
    this.getAllLevels();
  }
  
  public onChangeLevel4(event: any) {
    let fullClassName = this.level1.value + '\\' + this.level2.value + '\\' + this.level3.value + '\\' + <string> event.value;
    this.change.emit(fullClassName);
    this.getAllLevels();
  }  
  
  async getAllLevels(){
    this.allLevel1 = [];
    this.allLevel2 = [];
    this.allLevel3 = [];
    this.allLevel4 = [];

    this.allLevel1 = await this.getPackage();

    if(this.level1.value && this.level1.value != '*') {
      this.allLevel2 = await this.getPath(this.level1.value);
    }
    
    if(this.level2.value && this.level2.value != '*' && this.level2.value == this.level2.value.toLowerCase()) {
      this.allLevel3 = await this.getPath(this.level1.value, this.level2.value);
    }

    if(this.level3.value && this.level3.value != '*' && this.level3.value == this.level3.value.toLowerCase()) {
      this.allLevel4 = await this.getPath(this.level1.value, this.level2.value + '/' + this.level3.value);  
    }
  }



  async getPath(thePackage:string, thePath:string = ''){
    let classes = await this.getClasses(thePackage, thePath);
    classes = classes.filter((element:any)=> !element.includes('\\'));
    let namespaces = await this.getNameSpaces(thePackage, thePath);
    namespaces = namespaces.filter((element:any)=> !element.includes('\\'));
    let concat = classes.concat(namespaces);
    concat.push('*');
    return concat;
  }





  public async getPackage() {
    let result: any = [];
    try {
      const data:any = await this.api.fetch('/?get=config_packages');
      if(data && data.length) {
        result = data;
      }
    }  
    catch (error) {
    }
    result.push('*');
    return result;
  }

  public async getClasses(package_name:string, path:string = ""){
    let result: any = [];
    try {
      const data:any = await this.api.fetch('/?get=config_classes', {
        package: package_name,
        path : path
      });
      if(data && data.length) {
        result = data;
      }
    }  
    catch (error) {
    }
    return result;
  }

  public async getNameSpaces(package_name:string, path:string=""){
    let result: any = [];
    try {
      const data:any = await this.api.fetch('/?get=config_namespaces', {
        package: package_name,
        path : path
      });
      if(data && data.length) {
        result = data;
      }
    }  
    catch (error) {
    }
    return result;
  }
}
