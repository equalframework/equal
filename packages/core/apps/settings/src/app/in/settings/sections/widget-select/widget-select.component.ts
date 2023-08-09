import { Component, Input, OnInit } from '@angular/core';
import { FormControl } from '@angular/forms';
import { pairwise, startWith } from 'rxjs/operators';
import { SettingService } from 'src/app/settingService';

@Component({
  selector: 'app-widget-select',
  templateUrl: './widget-select.component.html',
  styleUrls: ['./widget-select.component.scss']
})
export class WidgetSelectComponent implements OnInit {

  constructor(public service: SettingService) { }
  @Input() choices: any[];
  @Input() setting: any;

  public settingValue: any;
  public settingName: any;

  public control = new FormControl();
  public focusState: any;
  public previousValue: any;

  ngOnInit(): void {

    this.settingValue = this.setting.setting_values_ids[0].value;
    this.settingName = this.setting.code;

    this.control.valueChanges.pipe(
      startWith(this.settingValue),
      pairwise()
    ).subscribe(
      ([old, value]) => {
        this.settingValue = value;
        this.previousValue = old;
      }
    )
    this.control.setValue(this.settingValue);
  }

  public onChange(eventValue: any) {
    //use the service to add the elements
    this.service.toQueue(this.setting.setting_values_ids[0].id, { newValue: this.settingValue, oldValue: this.previousValue }).subscribe((action) => {
      if (action == 'undo') {
        this.control.setValue(this.previousValue);
      }
    });
  }
}
