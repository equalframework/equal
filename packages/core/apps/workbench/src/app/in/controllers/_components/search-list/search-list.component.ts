import { Component, EventEmitter, Input, OnInit, Output, ViewEncapsulation } from '@angular/core';
import { MatIconModule } from '@angular/material/icon';
import { MatBadgeModule } from '@angular/material/badge';
import { MatDialog } from '@angular/material/dialog';

@Component({
  selector: 'app-search-list',
  templateUrl: './search-list.component.html',
  styleUrls: ['./search-list.component.scss'],
  encapsulation: ViewEncapsulation.None
})
export class SearchListComponent implements OnInit {

    @Input() data: any;
    @Input() selected_node: any;
    @Output() nodeSelect = new EventEmitter<string>();

    public inputValue: string;
    public filteredData: any;

    constructor(private dialog: MatDialog) { }

    public ngOnInit(): void {
        this.onSearch('');
    }

    public ngOnChanges() {
        this.onSearch('');
    }

    /**
     * Will update filterData with the new filter.
     *
     * @param value value of the filter
     */
    public onSearch(value: string) {
        this.filteredData = this.data.filter((node: any) => node.toLowerCase().includes(value.toLowerCase()));
    }

    /**
     * Notify parent component of the selected node.
     *
     * @param node value of the node which is clicked on
     */
    public onclickNodeSelect(node: string){
        this.nodeSelect.emit(node);
    }
}
