<div>
    @if (count($resources))
    <table
        class="table w-full"
        class="{{$tableStyle}} {{$showColumnBorders ? 'table-grid' : ''}}"
        cellpadding="0"
        cellspacing="0"
    >
        <thead>
        <tr>
            <!-- Select Checkbox -->
            <!-- todo : add checkboxes -->
{{--            <th class="w-16" v-if="shouldShowCheckboxes">&nbsp;</th>--}}

            <!-- Field Names -->
            @foreach($resources->first()->fields as $field)
            <th class="{{$field->textAlign}}">
                @if ($field->sortable)
                    <!-- todo : handle sort click -->
                    <span
                        class="cursor-pointer inline-flex items-center"
                    >
                        {{ $field->name }}

                        <svg
                            class="ml-2 flex-no-shrink"
                            xmlns="http://www.w3.org/2000/svg"
                            width="8"
                            height="14"
                            viewBox="0 0 8 14"
                        >
                            <path
                                class="{{$field->descSorted() ? 'fill-80' : 'fill-60'}}"
                                d="M1.70710678 4.70710678c-.39052429.39052429-1.02368927.39052429-1.41421356 0-.3905243-.39052429-.3905243-1.02368927 0-1.41421356l3-3c.39052429-.3905243 1.02368927-.3905243 1.41421356 0l3 3c.39052429.39052429.39052429 1.02368927 0 1.41421356-.39052429.39052429-1.02368927.39052429-1.41421356 0L4 2.41421356 1.70710678 4.70710678z"
                            />
                            <path
                                class="{{$field->ascSorted() ? 'fill-80' : 'fill-60'}}"
                                d="M6.29289322 9.29289322c.39052429-.39052429 1.02368927-.39052429 1.41421356 0 .39052429.39052429.39052429 1.02368928 0 1.41421358l-3 3c-.39052429.3905243-1.02368927.3905243-1.41421356 0l-3-3c-.3905243-.3905243-.3905243-1.02368929 0-1.41421358.3905243-.39052429 1.02368927-.39052429 1.41421356 0L4 11.5857864l2.29289322-2.29289318z"
                            />
                        </svg>
                    </span>
                @else
                        <span v-else>{{ $field->name }}</span>
                @endif
            </th>
            @endforeach

            <!-- Actions, View, Edit, Delete -->
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        @foreach($resources as $resource)
            <tr>
                @foreach($resource->fields as $field)
                    @if (data_get($field, 'meta.asHtml') === true)
                        <td>{!! $field->value !!}</td>
                    @else
                        <td>{{$field->value}}</td>
                    @endif
                @endforeach
            </tr>
        @endforeach
{{--        <tr--}}
{{--            v-for="(resource, index) in resources"--}}
{{--            @actionExecuted="$emit('actionExecuted')"--}}
{{--            :testId="`${resourceName}-items-${index}`"--}}
{{--            :key="resource.id.value"--}}
{{--            :delete-resource="deleteResource"--}}
{{--            :restore-resource="restoreResource"--}}
{{--            is="resource-table-row"--}}
{{--            :resource="resource"--}}
{{--            :resource-name="resourceName"--}}
{{--            :relationship-type="relationshipType"--}}
{{--            :via-relationship="viaRelationship"--}}
{{--            :via-resource="viaResource"--}}
{{--            :via-resource-id="viaResourceId"--}}
{{--            :via-many-to-many="viaManyToMany"--}}
{{--            :checked="selectedResources.indexOf(resource) > -1"--}}
{{--            :actions-are-available="actionsAreAvailable"--}}
{{--            :actions-endpoint="actionsEndpoint"--}}
{{--            :should-show-checkboxes="shouldShowCheckboxes"--}}
{{--            :update-selection-status="updateSelectionStatus"--}}
{{--        />--}}
        </tbody>
    </table>
    @endif
</div>
